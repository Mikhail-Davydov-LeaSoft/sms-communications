<?php

namespace FmTod\SmsCommunications\Services;

use FmTod\SmsCommunications\Contracts\ProcessesSMS;
use FmTod\SmsCommunications\DataTransferObjects\MessageData;
use FmTod\SmsCommunications\Exceptions\CouldNotSendMessage;
use Vonage\Messages\Channel\Message;
use Vonage\Messages\Channel\MMS\MMSAudio;
use Vonage\Messages\Channel\MMS\MMSImage;
use Vonage\Messages\Channel\MMS\MMSvCard;
use Vonage\Messages\Channel\MMS\MMSVideo;
use Vonage\Messages\MessageObjects\AudioObject;
use Vonage\Messages\MessageObjects\ImageObject;
use Vonage\Messages\MessageObjects\VCardObject;
use Vonage\Messages\MessageObjects\VideoObject;

class Nexmo implements ProcessesSMS
{
    private \Vonage\Client $client;

    private \Vonage\Client\Credentials\Basic $basic;

    private string $message_type;

    public function __construct(NexmoCredentials $configuration)
    {
        $apiKey = $configuration->api_key;
        $api_secret = $configuration->api_secret;
        $this->basic = new \Vonage\Client\Credentials\Basic($apiKey, $api_secret);
        $this->client = new \Vonage\Client($this->basic);
    }

    public function sendMessage(array $messageData)
    {
        $messageData['to'] = str_replace('+', '', $messageData['to']);
        $messageData['from'] = str_replace('+', '', $messageData['from']);

        try {
            if (! empty($messageData['body'])) {
                // SMS
                $this->client->sms()->send(new \Vonage\SMS\Message\SMS($messageData['to'], $messageData['from'], $messageData['body']));
                $this->message_type = 'text';
            } else {
                // MMS
                $this->client->messages()->send($this->getTypedMessage($messageData));
            }
        } catch (\Exception $e) {
            throw new CouldNotSendMessage('Message was not sent due to an error '.$e->getMessage(), $e);
        }

        return new MessageData(...[
            'to' => $messageData['to'],
            'from' => $messageData['from'],
            'message_type' => $this->message_type,
            // ToDo set correct service message id
            'service_message_id' => '',
        ]);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     * @throws \Vonage\Client\Exception\Request
     * @throws \Vonage\Client\Exception\Server
     */
    public function getStandardNumberInsight($number): \Vonage\Insights\Standard
    {
        return $this->client->insights()->standard($number);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Vonage\Client\Exception\Exception
     * @throws \Vonage\Client\Exception\Request
     * @throws \Vonage\Client\Exception\Server
     */
    public function getNumberData($number): \Vonage\Numbers\Number
    {
        return $this->client->numbers()->get($number);
    }

    protected function getTypedMessage(array $messageData): Message
    {
        if (! empty($messageData['type'])) {
            $this->message_type = $messageData['type'];
        } else {
            $ext = $messageData['file']->getClientOriginalExtension();
            $dir_path = config('sms-communications.mms.path');

            foreach (config('sms-communications.mms.ext') as $type => $arr) {
                if (in_array($ext, $arr)) {
                    $this->message_type = $type;
                }
            }
        }

        $fileUrl = asset("storage/{$dir_path}/{$messageData['fileName']}");

        switch ($this->message_type) {
            case 'image':
                $typedMessage = new MMSImage($messageData['to'], $messageData['from'], new ImageObject($fileUrl));
                break;
            case 'audio':
                $typedMessage = new MMSAudio($messageData['to'], $messageData['from'], new AudioObject($fileUrl));
                break;
            case 'video':
                $typedMessage = new MMSVideo($messageData['to'], $messageData['from'], new VideoObject($fileUrl));
                break;
            case 'document':
                $typedMessage = new MMSvCard($messageData['to'], $messageData['from'], new VCardObject($fileUrl));
                break;
            default: $typedMessage = null;
        }

        return $typedMessage;
    }
}
