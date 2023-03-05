<?php

namespace FmTod\SmsCommunications\Tests;

use FmTod\SmsCommunications\SmsCommunicationsServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;
use Propaganistas\LaravelPhone\PhoneServiceProvider;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'FmTod\\SmsCommunications\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            SmsCommunicationsServiceProvider::class,
            PhoneServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
    }

    protected function beforeRefreshingDatabase(): void
    {
        $this->loadLaravelMigrations();
        $this->artisan('vendor:publish', ['--tag' => 'sms-communications-migrations']);
    }
}
