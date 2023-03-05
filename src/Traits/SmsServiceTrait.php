<?php

namespace FmTod\SmsCommunications\Traits;

use FmTod\SmsCommunications\Contracts\ProcessesSMS;
use FmTod\SmsCommunications\Exceptions\InvalidServiceAccount;
use FmTod\SmsCommunications\Models\Account;
use FmTod\SmsCommunications\Services\BryteCall;
use FmTod\SmsCommunications\Services\BryteCallCredentials;
use FmTod\SmsCommunications\Services\Nexmo;
use FmTod\SmsCommunications\Services\NexmoCredentials;
use FmTod\SmsCommunications\Services\WhatsApp;
use FmTod\SmsCommunications\Services\WhatsAppCredentials;
use Illuminate\Support\Facades\Storage;

trait SmsServiceTrait
{
    public function getService(Account $account): ProcessesSMS
    {
        return match ($account->name) {
            'nexmo' => new Nexmo(NexmoCredentials::from($account->credentials)),
            'brytecall' => new BryteCall(BryteCallCredentials::from($account->credentials)),
            'whatsapp' => new WhatsApp(WhatsAppCredentials::from($account->credentials)),
            default => throw new InvalidServiceAccount('Provided account does not have a valid service'),
        };
    }

    /**
     * Get all extensions
     *
     * @return array Extensions of all file types
     */
    private function allExtensions(): array
    {
        return array_merge(
            config('sms-communications.mms.ext.image'),
            config('sms-communications.mms.ext.video'),
            config('sms-communications.mms.ext.audio'),
            config('sms-communications.mms.ext.document')
        );
    }

    public function uploadFile($file): string|false
    {
        $folder = config('sms-communications.mms.path');
        $storage = config('sms-communications.mms.disk');
        $filename = uniqid('', true).'_'.str_replace(' ', '_', $file->getClientOriginalName());

        $path = Storage::disk($storage)->putFileAs($folder, $file, $filename);

        return $path ? $filename : false;
    }

    public function uploadFileFromUrl($url): string|false
    {
        $folder = config('sms-communications.mms.path');
        $storage = config('sms-communications.mms.disk');
        $filename = basename($url);

        $result = Storage::disk($storage)->put("$folder/$filename", file_get_contents($url));

        return $result ? $filename : false;
    }
}
