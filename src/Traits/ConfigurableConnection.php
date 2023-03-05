<?php

namespace FmTod\SmsCommunications\Traits;

trait ConfigurableConnection
{
    protected function connectionName(): ?string
    {
        return config('sms-communications.database_connection');
    }

    protected function table(string $configKey): string
    {
        return app(config("sms-communications.models.$configKey"))->getTable();
    }
}
