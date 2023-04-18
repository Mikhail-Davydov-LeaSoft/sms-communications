<?php

use FmTod\SmsCommunications\Models\PhoneNumber;
use function Pest\Laravel\put;

beforeEach(function () {
    actingAsUser();
});

it('blocks client', function () {
    $phoneNumber = factory('phone_number')->create();
    put('/sms-communications/contacts/'.$phoneNumber->id)->assertSuccessful();

    $phoneNumberTest = PhoneNumber::find($phoneNumber->id);
    expect($phoneNumberTest->blocked_at)->not->toBeNull();
});

it('unblocks client', function () {
    $phoneNumber = factory('phone_number')->create(['blocked_at' => date('Y-m-d H:i:s')]);
    put('/sms-communications/contacts/'.$phoneNumber->id)->assertSuccessful();

    $phoneNumberTest = PhoneNumber::find($phoneNumber->id);
    expect($phoneNumberTest->blocked_at)->toBeNull();
});
