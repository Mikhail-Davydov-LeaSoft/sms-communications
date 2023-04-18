<?php

namespace FmTod\SmsCommunications\Services;

use FmTod\SmsCommunications\Concerns\MockableService;
use FmTod\SmsCommunications\Contracts\ProcessesSMS;
use FmTod\SmsCommunications\DataTransferObjects\MessageData;
use FmTod\SmsCommunications\Exceptions\CouldNotSendMessage;
use FmTod\SmsCommunications\Exceptions\CouldNotSetWebhookUrl;
use Illuminate\Support\Facades\Http;

class BryteCall implements ProcessesSMS
{
    use MockableService;

    private string $apiEndpoint;

    private string $dir_path;

    private string $accessToken;

    private string $accountUid;

    public function __construct(BryteCallCredentials $configuration)
    {
        $this->accountUid = $configuration->accountUid;
        $this->accessToken = $configuration->accessToken;
        $this->apiEndpoint = config('sms-communications.endpoints.brytecall');
        $this->dir_path = config('sms-communications.mms.path');
    }

    public function sendMessage(array $messageData)
    {
        $messageType = '';
        $data = [
            'to' => $messageData['to'],
            'from' => $messageData['from'],
        ];

        if (! empty($messageData['body'])) {
            // SMS
            $messageType = 'text';
            $data['message'] = $messageData['body'];
        } else {
            // MMS
            if (! empty($messageData['type'])) {
                $messageType = $messageData['type'];
            } else {
                $ext = $messageData['file']->getClientOriginalExtension();

                foreach (config('sms-communications.mms.ext') as $type => $arr) {
                    if (in_array($ext, $arr)) {
                        $messageType = $type;
                    }
                }
            }

            $fileUrl = asset("storage/{$this->dir_path}/{$messageData['fileName']}");
            $data['message'] = $messageType;
            $data['media'] = [
                $fileUrl,
            ];
        }

        try {
            $response = Http::withToken($this->accountUid.':'.$this->accessToken)->post($this->apiEndpoint.'/send', $data);
        } catch (\Exception $e) {
            throw new CouldNotSendMessage('Message was not sent due to an error '.$e->getMessage(), $e);
        }

        throw_if($response->failed(), CouldNotSendMessage::class, $response->body());

        return new MessageData(...[
            'to' => $messageData['to'],
            'from' => $messageData['from'],
            'message_type' => $messageType,
            // ToDo set correct service message id
            'service_message_id' => '',
        ]);
    }

    public function setWebhookUrl(): string
    {
        try {
            $response = Http::withToken($this->accountUid.':'.$this->accessToken)->post($this->apiEndpoint."/users/{$this->accountUid}/webhook", ['url' => url('/webhooks/inbound-message/brytecall')]);

            throw_if($response->failed(), CouldNotSetWebhookUrl::class, $response->body());
        } catch (\Exception $e) {
            throw new CouldNotSetWebhookUrl('Webhook url for incoming messages has not been set due to an error '.$e->getMessage(), $e);
        }

        return url('/webhooks/inbound-message/brytecall');
    }
}
