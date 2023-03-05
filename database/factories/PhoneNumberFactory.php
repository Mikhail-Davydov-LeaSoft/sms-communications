<?php

namespace FmTod\SmsCommunications\Database\Factories;

use FmTod\SmsCommunications\Models\Contact;
use FmTod\SmsCommunications\Models\PhoneNumber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\FmTod\SmsCommunications\Models\PhoneNumber>
 */
class PhoneNumberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PhoneNumber::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'value' => fake()->e164PhoneNumber(),
            'is_landline' => fake()->boolean,
            'can_receive_text' => fake()->boolean,
            'has_whatsapp' => fake()->boolean,
            'contact_id' => Contact::factory(),
        ];
    }
}
