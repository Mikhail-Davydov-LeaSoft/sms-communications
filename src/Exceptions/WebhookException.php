<?php

namespace FmTod\SmsCommunications\Exceptions;

use Exception;

class WebhookException extends Exception
{
    public function __construct(string $message, ?\Throwable $throwable = null)
    {
        parent::__construct($message, previous: $throwable);
    }
}
