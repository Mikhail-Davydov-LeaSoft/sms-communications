<?php

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

beforeEach(function () {
    actingAsUser();

    // Since this will be used in multiple tests, we can create it globally, but only for the tests on this file.
    $this->accountPhoneNumber = factory('account_phone_number')->create();
});

it('creates new conversation with existing phone numbers', function () {
    // Since this will only be used in this test, we can create it here.
    $phoneNumber = factory('phone_number')->create();

    $conversation = postJson('/sms-communications/conversations', [
        'account_phone_number_id' => $this->accountPhoneNumber->id,
        'phone_number_id' => $phoneNumber->id,
    ]);

    $conversation->assertSuccessful();

    assertDatabaseHas('conversations', [
        'id' => $conversation->getOriginalContent()->id,
    ]);
});

it('creates new conversation with manual phone numbers', function () {
    // Since we don't need to use an existing phone number the database won't have a phone row when running this test.

    $conversation = postJson('/sms-communications/conversations', [
        'account_phone_number_id' => $this->accountPhoneNumber->id,
        'phone_number' => '+380681111111',
    ]);

    $conversation->assertSuccessful();

    assertDatabaseHas('phone_numbers', [
        'value' => '+380681111111',
    ]);

    assertDatabaseHas('conversations', [
        'id' => $conversation->getOriginalContent()->id,
    ]);
});
