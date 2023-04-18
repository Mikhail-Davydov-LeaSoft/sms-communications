<?php

// config for FmTod/SmsCommunications
return [
    'path' => 'sms-communications/conversations',
    'middleware' => ['web'],

    'webhook' => [
        'path' => '{service}/webhook',
        'middleware' => [],
        'processors' => [
            'nexmo' => FmTod\SmsCommunications\Jobs\Webhooks\NexmoProcessWebhook::class,
            'brytecall' => FmTod\SmsCommunications\Jobs\Webhooks\BryteCallProcessWebhook::class,
            'whatsapp' => FmTod\SmsCommunications\Jobs\Webhooks\WhatsAppProcessWebhook::class,
        ],
        'whatsapp_hub_verify_token' => env('WHATSAPP_HUB_VERIFY_TOKEN'),
    ],
    'endpoints' => [
        'whatsapp' => 'https://graph.facebook.com/v15.0',
        'brytecall' => 'https://brytecall-txb.textable.app/api',
    ],
    'models' => [
        'account' => FmTod\SmsCommunications\Models\Account::class,
        'account_phone_number' => FmTod\SmsCommunications\Models\AccountPhoneNumber::class,
        'contact' => FmTod\SmsCommunications\Models\Contact::class,
        'phone_number' => FmTod\SmsCommunications\Models\PhoneNumber::class,
        'message' => FmTod\SmsCommunications\Models\Message::class,
        'conversation' => FmTod\SmsCommunications\Models\Conversation::class,
        'user' => Illuminate\Foundation\Auth\User::class,    ],

    'mms' => [
        'ext' => [
            'image' => ['jpg', 'jpeg', 'png', 'gif'],
            'video' => ['mp4'],
            'audio' => ['mp3', 'mpeg'],
            'document' => ['pdf', 'doc', 'docx', 'txt', 'html', 'htm', 'odt', 'xls', 'xlsx', 'ods', 'ppt', 'pptx', 'vcf'],
        ],
        'size' => '2M',
        'disk' => 'public',
        'path' => 'images/mms',
    ],

    /*
   * If set to false, no package routes and views will be available
   */
    'enabled' => env('SMS_COMMUNICATIONS_ENABLED', false),

    /*
     * This is the database connection that will be used by the migration and
     * the Activity model shipped with this package. In case it's not set
     * Laravel's database.default will be used instead.
     */
    'database_connection' => env('SMS_COMMUNICATIONS_DB_CONNECTION'),
    'nexmo_app_id' => env('NEXMO_APP_ID'),
];
