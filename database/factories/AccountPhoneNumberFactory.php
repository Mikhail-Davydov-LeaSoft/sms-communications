<?php

namespace FmTod\SmsCommunications\Database\Factories;

use FmTod\SmsCommunications\Models\Account;
use FmTod\SmsCommunications\Models\AccountPhoneNumber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\FmTod\SmsCommunications\Models\AccountPhoneNumber>
 */
class AccountPhoneNumberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AccountPhoneNumber::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'value' => fake()->e164PhoneNumber(),
        ];
    }
}
