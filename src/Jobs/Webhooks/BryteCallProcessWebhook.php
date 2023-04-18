<?php

namespace FmTod\SmsCommunications\Jobs\Webhooks;

use FmTod\SmsCommunications\Models\Account;
use FmTod\SmsCommunications\Models\AccountPhoneNumber;
use FmTod\SmsCommunications\Models\Conversation;
use FmTod\SmsCommunications\Models\Message;
use FmTod\SmsCommunications\Models\PhoneNumber;
use Illuminate\Http\Request;

class BryteCallProcessWebhook extends ProcessWebhookJob
{
    private array $requestData;

    public function __construct(Request $request)
    {
        $this->requestData = $request->all();
    }

    public function handle(): void
    {
        // SMS
        $accountIds = AccountPhoneNumber::where('value', $this->requestData['ToNumber'])->get()->pluck('account_id');
        $account = Account::where('name', 'brytecall')->whereIn('id', $accountIds)->first();
        $phoneNumber = PhoneNumber::where('value', '=', $this->requestData['FromNumber'])->first();

        $accountPhoneNumber = AccountPhoneNumber::where('value', $this->requestData['ToNumber'])
                                                ->where('account_id', $account->id)
                                                ->first();

        // Store PhoneNumber
        if (empty($phoneNumber)) {
            $phoneNumber = new PhoneNumber();
            $phoneNumber->value = $this->requestData['FromNumber'];
            $phoneNumber->is_landline = false;  // TODO Should be check
            $phoneNumber->can_receive_text = true;  // TODO Should be check
            $phoneNumber->has_whatsapp = false; // TODO Should be check

            $phoneNumber->save();
        }

        // For no blocked contacts
        if (is_null($phoneNumber->blocked_at)) {
            // Store Conversation
            $conversation = Conversation::where([
                'phone_number_id' => $phoneNumber->id,
                'account_phone_number_id' => $accountPhoneNumber->id,
            ])
            ->first();
            if (empty($conversation)) {
                $conversation = new Conversation();
                $conversation->phone_number_id = $phoneNumber->id;
                $conversation->account_phone_number_id = $accountPhoneNumber->id;
                $conversation->save();
            }

            // Store Message
            if (empty($this->requestData['Media'])) {
                $this->createMessage($conversation);

                return;
            }

            foreach ($this->requestData['Media'] as $media) {
                $this->createMessage($conversation, 'image', $media);
            }
        }
    }

    private function createMessage(Conversation $conversation, $messageType = 'text', $file_url = null)
    {
        $message = new Message();
        $message->conversation_id = $conversation->id;
        $message->is_incoming = true;
        $message->is_unread = true;
        $message->service_message_id = $this->requestData['MessageID'];
        $message->message_type = $messageType;
        $message->body = $this->requestData['MessageBody'] ?? null;
        if ($messageType === 'image') {
            $file_name = $this->uploadFileFromUrl($file_url);
            $message->file_name = $file_name ?: null;
        }
        $message->save();
    }

    public function setRequestData(array $requestData): void
    {
        $this->requestData = $requestData;
    }
}
