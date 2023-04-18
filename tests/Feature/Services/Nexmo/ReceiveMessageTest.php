<?php

use FmTod\SmsCommunications\Jobs\Webhooks\NexmoProcessWebhook;
use Illuminate\Support\Facades\Bus;
use Mockery\MockInterface;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->service = 'nexmo';
    $this->serviceMessageId = 'aaaaaaaa-bbbb-cccc-dddd-0123456789ab';
    $this->phoneNumber = '+380686666666';
    $this->apiKey = 'abcd1234';
    $this->account = factory('account')->create([
        'name' => $this->service,
        'credentials' => [
            'api_key' => $this->apiKey,
        ]]);
    $this->accountPhoneNumber = factory('account_phone_number')->create(['account_id' => $this->account->id]);
});

it('is dispatched', function () {
    Bus::fake();
    $response = $this->postJson("v1/client/webhooks/inbound-message/{$this->service}", ['msisdn' => $this->phoneNumber]);
    Bus::assertDispatched(NexmoProcessWebhook::class);
});

it('stores the incoming SMS', function () {
    $search = ['TO_NUMBER', 'FROM_NUMBER', 'API_KEY', 'MESSAGE_ID'];
    $replace = [
        str_replace('+', '', $this->accountPhoneNumber->value->formatE164()),
        $this->phoneNumber,
        $this->apiKey,
        $this->serviceMessageId,
    ];
    $json = str_replace($search, $replace, file_get_contents(__DIR__.'/ReceivedSmsPayload.json'));
    $payload = json_decode($json, true);

    $response = $this->postJson("v1/client/webhooks/inbound-message/{$this->service}", $payload);

    assertDatabaseHas('messages', [
        'service_message_id' => $this->serviceMessageId,
    ]);

    assertDatabaseHas('phone_numbers', [
        'value' => $this->phoneNumber,
    ]);
});

it('stores the incoming MMS', function () {
    $search = ['TO_NUMBER', 'FROM_NUMBER', 'API_KEY', 'MESSAGE_ID'];
    $replace = [
        str_replace('+', '', $this->accountPhoneNumber->value->formatE164()),
        $this->phoneNumber,
        $this->apiKey,
        $this->serviceMessageId,
    ];
    $json = str_replace($search, $replace, file_get_contents(__DIR__.'/ReceivedMmsPayload.json'));
    $payload = json_decode($json, true);

    $job = $this->partialMock(NexmoProcessWebhook::class, function (MockInterface $mock) {
        $mock->shouldReceive('uploadFileFromUrl')->once()
        ->andReturn("{$this->service}_mms.jpg");
    });

    $job->setRequestData($payload);
    $job->handle();

    assertDatabaseHas('messages', [
        'service_message_id' => $this->serviceMessageId,
        'file_name' => "{$this->service}_mms.jpg",
    ]);

    assertDatabaseHas('phone_numbers', [
        'value' => $this->phoneNumber,
    ]);
});
