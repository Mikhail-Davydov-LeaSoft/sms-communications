<?php

namespace FmTod\SmsCommunications\Database\Factories;

use FmTod\SmsCommunications\Models\AccountPhoneNumber;
use FmTod\SmsCommunications\Models\Conversation;
use FmTod\SmsCommunications\Models\PhoneNumber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\FmTod\SmsCommunications\Models\Conversation>
 */
class ConversationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Conversation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_phone_number_id' => AccountPhoneNumber::factory(),
            'phone_number_id' => PhoneNumber::factory(),
        ];
    }
}
