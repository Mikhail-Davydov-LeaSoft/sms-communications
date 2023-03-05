<?php

namespace FmTod\SmsCommunications\Services;

use FmTod\SmsCommunications\Exceptions\InvalidServiceCredentials;
use Spatie\LaravelData\Data;

class WhatsAppCredentials extends Data
{
    public function __construct(
        public string $accessToken,
        public string $phoneNumberId,
    ) {
    }

    public static function from(mixed ...$credentials): static
    {
        $credentials = current($credentials);
        throw_if(! isset($credentials['accessToken']), InvalidServiceCredentials::class, 'Provided credentials is missing the "accessToken".');
        throw_if(! isset($credentials['phoneNumberId']), InvalidServiceCredentials::class, 'Provided credentials is missing the "phoneNumberId".');

        return new static(
            accessToken: $credentials['accessToken'],
            phoneNumberId: $credentials['phoneNumberId'],
        );
    }
}
