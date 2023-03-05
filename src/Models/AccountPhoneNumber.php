<?php

namespace FmTod\SmsCommunications\Models;

use FmTod\SmsCommunications\Database\Factories\AccountPhoneNumberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;

class AccountPhoneNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'value',
    ];

    public $casts = [
        'value' => E164PhoneNumberCast::class,
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return AccountPhoneNumberFactory::new();
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function phoneNumber(): HasOne
    {
        return $this->hasOne(PhoneNumber::class, 'value', 'value');
    }
}
