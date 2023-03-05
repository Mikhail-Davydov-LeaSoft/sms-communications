<?php

use FmTod\SmsCommunications\Tests\TestCase;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\Factories\UserFactory;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(FmTod\SmsCommunications\Tests\TestCase::class)
->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Get factory for model with the specified key in the config.
 */
function factory($modelKey): Factory
{
    // ..
    /** @var ?\Illuminate\Database\Eloquent\Model&\Illuminate\Database\Eloquent\Factories\HasFactory $modelClass */
    $modelClass = config('sms-communications.models.'.$modelKey);

    if (! $modelClass) {
        throw new \RuntimeException('Model class not found for key: '.$modelKey);
    }

    if (! method_exists($modelClass, 'factory')) {
        throw new \RuntimeException('Model class does not have factory method: '.$modelKey);
    }

    return $modelClass::factory();
}

/**
 * Set the currently logged-in user for the application.
 */
function actingAs(Authenticatable $user, string $driver = null): TestCase
{
    return test()->actingAs($user, $driver);
}

/**
 * Set the currently logged-in user for the application.
 */
function actingAsUser(string $driver = null): TestCase
{
    $user = UserFactory::new()->create();

    return test()->actingAs($user, $driver);
}
