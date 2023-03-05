<?php

namespace FmTod\SmsCommunications\Services;

use FmTod\SmsCommunications\Exceptions\InvalidServiceCredentials;
use Spatie\LaravelData\Data;

class BryteCallCredentials extends Data
{
    public function __construct(
        public string $accountUid,
        public string $accessToken,
    ) {
    }

    public static function from(mixed ...$credentials): static
    {
        $credentials = current($credentials);
        throw_if(! isset($credentials['accountUid']), InvalidServiceCredentials::class, 'Provided credentials is missing the "accountUid".');
        throw_if(! isset($credentials['accessToken']), InvalidServiceCredentials::class, 'Provided credentials is missing the "accessToken".');

        return new static(
            accountUid: $credentials['accountUid'],
            accessToken: $credentials['accessToken'],
        );
    }
}
