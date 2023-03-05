<?php

namespace FmTod\SmsCommunications\Jobs\Webhooks;

use FmTod\SmsCommunications\Contracts\ProcessesWebhook;
use FmTod\SmsCommunications\Traits\SmsServiceTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

abstract class ProcessWebhookJob implements ShouldQueue, ProcessesWebhook
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SmsServiceTrait;

    abstract public function handle(): void;
}
