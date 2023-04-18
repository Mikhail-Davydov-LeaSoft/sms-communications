<?php

namespace FmTod\SmsCommunications\Database\Factories;

use FmTod\SmsCommunications\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\FmTod\SmsCommunications\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'identifier' => fake()->text(8),
            'credentials' => [
                'accessToken' => fake()->text(82),
                'phoneNumberId' => fake()->isbn13(),
            ],
        ];
    }
}
