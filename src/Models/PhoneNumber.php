<?php

namespace FmTod\SmsCommunications\Models;

use FmTod\SmsCommunications\Database\Factories\PhoneNumberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;

class PhoneNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'blocked_at',
        'value',
    ];

    protected $casts = [
        'value' => E164PhoneNumberCast::class,
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return PhoneNumberFactory::new();
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
