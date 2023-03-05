<?php

namespace FmTod\SmsCommunications\DataTransferObjects;

class MessageData
{
    public function __construct(
        public readonly string $to,
        public readonly string $from,
        public readonly string $message_type,
        public readonly string $service_message_id,
    ) {
    }
}
