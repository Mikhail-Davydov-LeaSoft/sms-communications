<?php

namespace FmTod\SmsCommunications\Jobs\Webhooks;

use FmTod\SmsCommunications\Models\Account;
use FmTod\SmsCommunications\Models\AccountPhoneNumber;
use FmTod\SmsCommunications\Models\Conversation;
use FmTod\SmsCommunications\Models\Message;
use FmTod\SmsCommunications\Models\PhoneNumber;
use Illuminate\Http\Request;

class NexmoProcessWebhook extends ProcessWebhookJob
{
    private \Vonage\Message\InboundMessage|\Vonage\SMS\Webhook\InboundSMS $message;

    private array $requestData;

    public function __construct(Request $request)
    {
        $this->requestData = $request->all();
    }

    public function handle(): void
    {
        if (! empty($this->requestData['channel']) && $this->requestData['channel'] === 'mms') {
            $this->message = new \Vonage\Message\InboundMessage($this->requestData['message_uuid']);

            $this->message->fromArray([
                'from' => $this->requestData['from'],
                'to' => $this->requestData['to'],
                'type' => $this->requestData['channel'] == 'sms' ? 'text' : $this->requestData['message_type'],
                'body' => $this->requestData['text'] ?? '',
                'file_url' => $this->requestData['channel'] == 'mms' ? $this->requestData[$this->requestData['message_type']]['url'] : '',
            ]);
        } else {
            $this->message = new \Vonage\SMS\Webhook\InboundSMS($this->requestData);
        }

        $from = '+'.$this->message->getFrom();
        $to = '+'.$this->message->getTo();

        if ($this->message instanceof \Vonage\Message\InboundMessage) {
            // Messages API
            $accountIds = AccountPhoneNumber::where('value', $to)->get()->pluck('account_id');
            $account = Account::where('name', 'nexmo')->whereIn('id', $accountIds)->first();

            $phoneNumber = PhoneNumber::where('value', '=', $from)->first();
        } else {
            // SMS API
            $account = Account::where('name', 'nexmo')
                              ->where('credentials', 'like', '%"api_key":"'.$this->message->getApiKey().'"%')
                              ->first();

            $phoneNumber = PhoneNumber::where('value', '=', $from)->first();
        }

        $accountPhoneNumber = AccountPhoneNumber::where('value', $to)
                                                ->where('account_id', $account->id)
                                                ->first();
        // Store PhoneNumber
        if (empty($phoneNumber)) {
            $phoneNumber = new PhoneNumber();
            $phoneNumber->value = $from;
            $phoneNumber->is_landline = false;
            $phoneNumber->can_receive_text = true;
            $phoneNumber->has_whatsapp = false; // TODO Should be check account type (like business or not)

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
            $message = new Message();
            $message->conversation_id = $conversation->id;
            $message->message_type = $this->message->getType();

            if ($this->message->getType() == 'text') {
                $message->body = $this->message->getText();
            } else {
                $file_name = $this->uploadFileFromUrl($this->message->toArray()['file_url']);
                $message->file_name = $file_name ?: null;
            }
            $message->is_incoming = true;
            $message->is_unread = true;
            $message->service_message_id = $this->message->getMessageId();
            $message->save();
        }
    }

    public function setRequestData(array $requestData): void
    {
        $this->requestData = $requestData;
    }
}
