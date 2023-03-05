<?php

namespace FmTod\SmsCommunications\Models;

use FmTod\SmsCommunications\Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'credentials' => 'array',
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return AccountFactory::new();
    }

    public function accountPhoneNumbers(): HasMany
    {
        return $this->hasMany(AccountPhoneNumber::class);
    }
}
