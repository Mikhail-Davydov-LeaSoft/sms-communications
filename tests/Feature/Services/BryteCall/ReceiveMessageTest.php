<?php

use FmTod\SmsCommunications\Jobs\Webhooks\BryteCallProcessWebhook;
use Illuminate\Support\Facades\Bus;
use Mockery\MockInterface;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->service = 'brytecall';
    $this->serviceMessageId = 'abc123xyz';
    $this->phoneNumber = '+380686666666';
    $this->account = factory('account')->create(['name' => $this->service]);
    $this->accountPhoneNumber = factory('account_phone_number')->create(['account_id' => $this->account->id]);
});

it('is dispatched', function () {
    Bus::fake();
    $response = $this->postJson("v1/client/webhooks/inbound-message/{$this->service}", []);
    Bus::assertDispatched(BryteCallProcessWebhook::class);
});

it('stores the incoming SMS', function () {
    $search = ['TO_NUMBER', 'FROM_NUMBER'];
    $replace = [
        $this->accountPhoneNumber->value->formatE164(),
        $this->phoneNumber,
    ];
    $json = str_replace($search, $replace, file_get_contents(__DIR__.'/ReceivedMessagePayload.json'));
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
    $search = ['TO_NUMBER', 'FROM_NUMBER'];
    $replace = [
        $this->accountPhoneNumber->value->formatE164(),
        $this->phoneNumber,
    ];
    $json = str_replace($search, $replace, file_get_contents(__DIR__.'/ReceivedMessagePayload.json'));
    $payload = json_decode($json, true);

    $payload['Media'] = [
        'https://sampleUrl.com/URL1',
    ];

    $job = $this->partialMock(BryteCallProcessWebhook::class, function (MockInterface $mock) {
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
