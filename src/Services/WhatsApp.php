<?php

namespace FmTod\SmsCommunications\Services;

use FmTod\SmsCommunications\Concerns\MockableService;
use FmTod\SmsCommunications\Contracts\ProcessesSMS;
use FmTod\SmsCommunications\DataTransferObjects\MessageData;
use FmTod\SmsCommunications\Exceptions\CouldNotSendMessage;
use Netflie\WhatsAppCloudApi\Message\Media\LinkID;
use Netflie\WhatsAppCloudApi\Response\ResponseException;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;

class WhatsApp implements ProcessesSMS
{
    use MockableService;

    private string $apiEndpoint;

    private string $dir_path;

    private WhatsAppCloudApi $whatsapp_cloud_api;

    public function __construct(WhatsAppCredentials $configuration)
    {
        $this->whatsapp_cloud_api = new WhatsAppCloudApi([
            'from_phone_number_id' => $configuration->phoneNumberId,
            'access_token' => $configuration->accessToken,
        ]);
        $this->apiEndpoint = config('sms-communications.endpoints.whatsapp');
        $this->dir_path = config('sms-communications.mms.path');
    }

    public function sendMessage(array $messageData)
    {
        $messageType = '';

        if (! empty($messageData['body'])) {
            // SMS
            $messageType = 'text';
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

            throw_if(! $messageType, CouldNotSendMessage::class, 'This file extension is not supported');

            $fileUrl = asset("storage/{$this->dir_path}/{$messageData['fileName']}");
            $link_id = new LinkID($fileUrl);
        }

        try {
            match ($messageType) {
                'text' => $response = $this->whatsapp_cloud_api->sendTextMessage(str_replace('+', '', $messageData['to']), $messageData['body']),
                'image' => $response = $this->whatsapp_cloud_api->sendImage(str_replace('+', '', $messageData['to']), $link_id),
                'document' => $response = $this->whatsapp_cloud_api->sendDocument(str_replace('+', '', $messageData['to']), $link_id, $messageData['file']->getClientOriginalName(), ''),
                'audio' => $response = $this->whatsapp_cloud_api->sendAudio(str_replace('+', '', $messageData['to']), $link_id),
                'video' => $response = $this->whatsapp_cloud_api->sendVideo(str_replace('+', '', $messageData['to']), $link_id),
            };
        } catch (ResponseException $e) {
            throw new CouldNotSendMessage('Message was not sent due to an error '.$e->getMessage(), $e);
        }

        return new MessageData(...[
            'to' => $messageData['to'],
            'from' => $messageData['from'],
            'message_type' => $messageType,
            'service_message_id' => $response->decodedBody()['messages'][0]['id'],
        ]);
    }
}
