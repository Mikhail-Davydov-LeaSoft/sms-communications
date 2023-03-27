<?php

use FmTod\SmsCommunications\DataTransferObjects\MessageData;
use FmTod\SmsCommunications\Http\Controllers\MessagesController;
use FmTod\SmsCommunications\Services\WhatsApp;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->account = factory('account')->create(['name' => 'whatsapp']);
    $this->accountPhoneNumber = factory('account_phone_number')->create(['account_id' => $this->account->id]);
    $this->conversation = factory('conversation')->create(['account_phone_number_id' => $this->accountPhoneNumber->id]);
});

it('can send sms', function () {
    WhatsApp::shouldSendMessage()->andReturnUsing(fn (array $messageData) => new MessageData(...[
        'to' => $messageData['to'],
        'from' => $messageData['from'],
        'message_type' => 'text',
        'service_message_id' => '123456',
    ]));

    $response = actingAsUser()->postJson('/sms-communications/messages', [
        'conversation_id' => $this->conversation->id,
        'channel' => 'sms',
        'body' => 'Hello World',
    ])->assertSuccessful();

    assertDatabaseHas('messages', [
        'id' => $response->getOriginalContent()->id,
    ]);
});

it('can send mms', function () {
    WhatsApp::shouldSendMessage()->andReturnUsing(fn (array $messageData) => new MessageData(...[
        'to' => $messageData['to'],
        'from' => $messageData['from'],
        'message_type' => 'image',
        'service_message_id' => '123456',
    ]));

    $mock = $this->partialMock(MessagesController::class, function (MockInterface $mock) {
        $mock->shouldReceive('uploadFile')->once();
    });

    Storage::fake(config('sms-communications.mms.disk'));
    $file = UploadedFile::fake()->image('mms_image.jpg');

    $response = actingAsUser()->postJson('/sms-communications/messages', [
        'conversation_id' => $this->conversation->id,
        'channel' => 'mms',
        'file' => $file,
    ])->assertSuccessful();

    assertDatabaseHas('messages', [
        'id' => $response->getOriginalContent()->id,
    ]);
});
