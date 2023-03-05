<?php

namespace FmTod\SmsCommunications\Database\Factories;

use FmTod\SmsCommunications\Models\Conversation;
use FmTod\SmsCommunications\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\FmTod\SmsCommunications\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'is_incoming' => fake()->boolean(),
            'is_unread' => fake()->boolean(),
            'body' => fake()->text(),
            'message_type' => fake()->randomElement(['text', 'image']),
            'is_pinned' => fake()->boolean(),
            'status' => fake()->randomElement(['sent', 'delivered', 'read', 'failed']),
        ];
    }
}
