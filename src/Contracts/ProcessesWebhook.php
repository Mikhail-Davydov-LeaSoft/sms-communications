<?php

namespace FmTod\SmsCommunications\Contracts;

use Illuminate\Http\Request;

interface ProcessesWebhook
{
    public function __construct(Request $request);

    public static function dispatch(...$arguments);

    public static function dispatchSync(...$arguments);

    public function handle(): void;
}
