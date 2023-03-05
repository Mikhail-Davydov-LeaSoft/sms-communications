<?php

namespace FmTod\SmsCommunications\Contracts;

interface ProcessesSMS
{
    public function sendMessage(array $messageData);
}
