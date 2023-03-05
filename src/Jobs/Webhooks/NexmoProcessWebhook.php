<?php

namespace FmTod\SmsCommunications\Jobs\Webhooks;

use FmTod\SmsCommunications\Models\Account;
use FmTod\SmsCommunications\Models\AccountPhoneNumber;
use FmTod\SmsCommunications\Models\Conversation;
use FmTod\SmsCommunications\Models\Message;
use FmTod\SmsCommunications\Models\PhoneNumber;
use FmTod\SmsCommunications\Services\Nexmo;
use FmTod\SmsCommunications\Services\NexmoCredentials;
use Illuminate\Http\Request;

class NexmoProcessWebhook extends ProcessWebhookJob
{
    private \Vonage\Message\InboundMessage|\Vonage\SMS\Webhook\InboundSMS $message;

    public function __construct(Request $request)
    {
        $requestData = $request->all();

        if (! empty($requestData['channel']) && $requestData['channel'] === 'mms') {
            $this->message = new \Vonage\Message\InboundMessage($requestData['message_uuid']);

            $this->message->fromArray([
                'from' => $requestData['from'],
                'to' => $requestData['to'],
                'type' => $requestData['channel'] == 'sms' ? 'text' : $requestData['message_type'],
                'body' => $requestData['text'] ?? '',
                'file_url' => $requestData['channel'] == 'mms' ? $requestData[$requestData['message_type']]['url'] : '',
            ]);
        } else {
            $this->message = new \Vonage\SMS\Webhook\InboundSMS($requestData);
        }
    }

    public function handle(): void
    {
        if ($this->message instanceof \Vonage\Message\InboundMessage) {
            // Messages API
            $accountIds = AccountPhoneNumber::where('value', $this->message->getTo())->get()->pluck('account_id');
            $account = Account::where('name', 'nexmo')->whereIn('id', $accountIds)->first();

            $credentials = NexmoCredentials::from($account->credentials);
            $phoneNumber = PhoneNumber::where('value', '=', $this->message->getFrom())->first();
            $from = $this->message->getFrom();
        } else {
            // SMS API
            $account = Account::where('name', 'nexmo')
                              ->where('credentials', 'like', '%"api_key":"'.$this->message->getApiKey().'"%')
                              ->first();
            $credentials = NexmoCredentials::from($account->credentials);
            $phoneNumber = PhoneNumber::where('value', '=', $this->message->getMsisdn())->first();

            $from = $this->message->getMsisdn();
        }

        $accountPhoneNumber = AccountPhoneNumber::where('value', $this->message->getTo())
                                                ->where('account_id', $account->id)
                                                ->first();

        // Store PhoneNumber
        if (empty($phoneNumber)) {
            $service = new Nexmo($credentials);
            $numberStandartData = $service->getStandardNumberInsight($from);
            $numberData = $service->getNumberData($from);

            $phoneNumber = new PhoneNumber();
            $phoneNumber->value = $from;
            $phoneNumber->is_landline = in_array($numberStandartData->getCurrentCarrier()['network_type'], ['landline', 'landline_premium', 'landline_tollfree']) ? true : false;
            $phoneNumber->can_receive_text = in_array('SMS', $numberData['features']) ? true : false;
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
            if ($message->message_type == 'text') {
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
}
