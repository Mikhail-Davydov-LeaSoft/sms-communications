<?php

namespace FmTod\SmsCommunications\Services;

use FmTod\SmsCommunications\Exceptions\InvalidServiceCredentials;
use Spatie\LaravelData\Data;

class NexmoCredentials extends Data
{
    public function __construct(
        public string $api_key,
        public string $api_secret,
    ) {
    }

    public static function from(mixed ...$credentials): static
    {
        $credentials = current($credentials);
        throw_if(! isset($credentials['api_key']), InvalidServiceCredentials::class, 'Provided credentials is missing the "api_key".');
        throw_if(! isset($credentials['api_secret']), InvalidServiceCredentials::class, 'Provided credentials is missing the "api_secret".');

        return new static(
            api_key: $credentials['api_key'],
            api_secret: $credentials['api_secret'],
        );
    }
}
