<?php

namespace FmTod\SmsCommunications\Models;

use FmTod\SmsCommunications\Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_unread',
        'conversation_id',
        'is_incoming',
        'service_message_id',
        'message_type',
        'body',
        'file_name',
    ];

    protected $casts = [
        'is_unread' => 'boolean',
        'is_pinned' => 'boolean',
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return MessageFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('sms-communications.models.user'), 'user_id');
    }
}
