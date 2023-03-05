<?php

namespace FmTod\SmsCommunications\Jobs\Webhooks;

use FmTod\SmsCommunications\Exceptions\WebhookException;
use FmTod\SmsCommunications\Models\AccountPhoneNumber;
use FmTod\SmsCommunications\Models\Conversation;
use FmTod\SmsCommunications\Models\Message;
use FmTod\SmsCommunications\Models\PhoneNumber;
use FmTod\SmsCommunications\Services\WhatsAppCredentials;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Netflie\WhatsAppCloudApi\WebHook;
use Netflie\WhatsAppCloudApi\WebHook\Notification\Media;
use Netflie\WhatsAppCloudApi\WebHook\Notification\MessageNotification;
use Netflie\WhatsAppCloudApi\WebHook\Notification\StatusNotification;
use Netflie\WhatsAppCloudApi\WebHook\Notification\Text;

class WhatsAppProcessWebhook extends ProcessWebhookJob
{
    private WhatsAppCredentials $credentials;

    private array $requestData;

    public function __construct(Request $request)
    {
        $this->requestData = $request->all();
    }

    /**
     * @throws \FmTod\SmsCommunications\Exceptions\WebhookException
     */
    public function handle(): void
    {
        $notification = (new WebHook)->read($this->requestData);

        if ($notification instanceof StatusNotification) {
            $this->updateMessageStatus($notification);

            return;
        }

        if ($notification instanceof MessageNotification) {
            $this->receiveMessage($notification);

            return;
        }

        throw new WebhookException('Unknown notification type');
    }

    private function getMediaById($id)
    {
        $response = Http::withToken($this->credentials->accessToken)
            ->get(config('sms-communications.endpoints.whatsapp').'/'.$id)
            ->onError(function (Response $error) {
                throw new WebhookException('Could not retrieve media from WhatsApp: '.$error->body(), $error->toException());
            });

        return $response->object();
    }

    /**
     * @throws \FmTod\SmsCommunications\Exceptions\WebhookException
     */
    private function updateMessageStatus(StatusNotification $notification): void
    {
        if (Message::where('service_message_id', $notification->id())->doesntExist()) {
            throw new WebhookException('Could not find message with service_message_id: '.$notification->id().' to update status');
        }

        Message::where('service_message_id', $notification->id())
            ->update(['status' => $notification->status()]);
    }

    /**
     * @throws \FmTod\SmsCommunications\Exceptions\WebhookException
     */
    private function receiveMessage(MessageNotification $notification): void
    {
        $accountPhoneNumber = AccountPhoneNumber::where('value', '+'.$notification->businessPhoneNumber())
            ->withWhereHas('account', fn (Builder $query) => $query->where('name', 'whatsapp'))
            ->firstOrFail();

        $this->credentials = WhatsAppCredentials::from($accountPhoneNumber->account->credentials);

        if (! $notification->customer()) {
            throw new WebhookException('Could not retrieve customer from notification');
        }

        $phoneNumber = PhoneNumber::firstOrCreate([
            'value' => '+'.$notification->customer()->phoneNumber(),
        ], [
            'is_landline' => false,
            'can_receive_text' => true,
            'has_whatsapp' => true,
        ]);

        // For no blocked contacts
        if (isset($phoneNumber->blocked_at)) {
            return;
        }

        // Store Conversation
        $conversation = Conversation::firstOrCreate([
            'phone_number_id' => $phoneNumber->id,
            'account_phone_number_id' => $accountPhoneNumber->id,
        ]);

        if ($notification instanceof Media) {
            $media = $this->getMediaById($notification->imageId());
            $mimeType = explode('/', $notification->mimeType());

            $file = Http::withToken($this->credentials->accessToken)->get($media->url);

            $fileName = 'whatsapp_'.$conversation->id.'_'.uniqid('', true).'.'.$mimeType[1];
            $result = Storage::disk('public')->put('images/mms/'.$fileName, $file);

            Message::create([
                'conversation_id' => $conversation->id,
                'is_incoming' => true,
                'is_unread' => true,
                'service_message_id' => $notification->id(),
                'message_type' => $mimeType[0],
                'file_name' => ($fileName && $result) ? $fileName : null,
            ]);

            return;
        }

        if ($notification instanceof Text) {
            Message::create([
                'conversation_id' => $conversation->id,
                'is_incoming' => true,
                'is_unread' => true,
                'service_message_id' => $notification->id(),
                'message_type' => 'text',
                'body' => $notification->message(),
            ]);

            return;
        }

        throw new WebhookException('Unknown message type');
    }
}
