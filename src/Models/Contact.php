<?php

namespace FmTod\SmsCommunications\Models;

use FmTod\SmsCommunications\Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return ContactFactory::new();
    }

    public function phoneNumbers(): hasMany
    {
        return $this->hasMany(PhoneNumber::class);
    }
}
