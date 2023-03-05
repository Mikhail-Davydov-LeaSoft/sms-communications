<?php

namespace FmTod\SmsCommunications;

use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SmsCommunicationsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('sms-communications')
            ->hasConfigFile('sms-communications')
            ->hasViews()
            ->hasAssets()
            ->hasRoute('web')
            ->hasRoute('api')
            ->hasMigration('create_contacts_table')
            ->hasMigration('create_phone_numbers_table')
            ->hasMigration('create_accounts_table')
            ->hasMigration('create_account_phone_numbers_table')
            ->hasMigration('create_conversations_table')
            ->hasMigration('create_messages_table')
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations();
            });
    }

    /**
     * Bootstrap any package services.
     */
    public function bootingPackage(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/ts' => resource_path('ts'),
            ], 'sms-communications-ts');
            $this->publishes([__DIR__.'/../public' => public_path('vendor/sms-communications')], ['sms-communications-assets', 'laravel-assets']);
        }
    }
}
