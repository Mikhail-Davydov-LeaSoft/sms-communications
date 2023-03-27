<?php

use FmTod\SmsCommunications\Jobs\Webhooks\WhatsAppProcessWebhook;
use Illuminate\Support\Facades\Bus;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->service = 'whatsapp';
    $this->serviceMessageId = 'wamid.HBgMMzgwNjg2MTM4OTYzFQIAERgSODMyQjdDODkzNEUyNTlBODE0AA==';
    $this->account = factory('account')->create(['name' => 'whatsapp']);
    $this->accountPhoneNumber = factory('account_phone_number')->create(['account_id' => $this->account->id]);
});

it('is dispatched', function () {
    Bus::fake();
    $response = $this->postJson("v1/client/webhooks/inbound-message/{$this->service}", []);
    Bus::assertDispatched(WhatsAppProcessWebhook::class);
});

it('stores the incoming message', function () {
    $json = str_replace('BUSINESS_DISPLAY_PHONE_NUMBER', str_replace('+', '', $this->accountPhoneNumber->value->formatE164()), file_get_contents(__DIR__.'/ReceivedMessagePayload.json'));
    $payload = json_decode($json, true);
    $response = $this->postJson("v1/client/webhooks/inbound-message/{$this->service}", $payload);

    assertDatabaseHas('messages', [
        'service_message_id' => $this->serviceMessageId,
        'body' => 'WhatsApp webhook message text',
    ]);
});

it('updates the status of an existing messages', function () {
    $message = factory('message')->create([
        'service_message_id' => $this->serviceMessageId,
        'status' => 'sent',
    ]);

    $search = ['BUSINESS_DISPLAY_PHONE_NUMBER', 'WHATSAPP_MESSAGE_ID', 'TIMESTAMP'];
    $replace = [
        str_replace('+', '', $this->accountPhoneNumber->value->formatE164()),
        $this->serviceMessageId,
        now()->timestamp,
    ];
    $json = str_replace($search, $replace, file_get_contents(__DIR__.'/MessageStatusNotification.json'));
    $payload = json_decode($json, true);

    $response = $this->postJson("v1/client/webhooks/inbound-message/{$this->service}", $payload);

    assertDatabaseHas('messages', [
        'service_message_id' => $this->serviceMessageId,
        'status' => 'read',
    ]);
});

it('verify subscription request', function () {
    Bus::fake();
    $payload = [
        'hub.mode' => 'subscribe',
        'hub.challenge' => 'challenge',
        'hub.verify_token' => config('sms-communications.webhook.whatsapp_hub_verify_token'),
    ];

    $response = $this->postJson("v1/client/webhooks/inbound-message/{$this->service}", $payload)
        ->assertSuccessful();
});
