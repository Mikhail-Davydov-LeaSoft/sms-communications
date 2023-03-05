<?php

use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

beforeEach(function () {
    actingAsUser();

    $this->messages = factory('message')->count(3)->create();
});

it('it can delete messages', function () {
    postJson('/sms-communications/messages/delete', [
        'ids' => $this->messages->pluck('id')->toArray(),
    ])->assertSuccessful();

    assertDatabaseMissing('messages', [
        'id' => '1',
        'id' => '3',
        'id' => '5',
    ]);
});

it('can pin messages', function () {
    postJson('/sms-communications/messages/pin', [
        'ids' => $this->messages->pluck('id')->toArray(),
    ])->assertSuccessful();

    $this->messages->each->refresh();

    expect($this->messages)->each(fn ($message) => $message->is_pinned->toBeTrue());
});

it('can unpin messages', function () {
    postJson('/sms-communications/messages/unpin', [
        'ids' => $this->messages->pluck('id')->toArray(),
    ])->assertSuccessful();

    $this->messages->each->refresh();

    expect($this->messages)->each(fn ($message) => $message->is_pinned->toBeFalse());
});

it('can mark messages as unread', function () {
    postJson('/sms-communications/messages/unread', [
        'ids' => $this->messages->pluck('id')->toArray(),
    ])->assertSuccessful();

    $this->messages->each->refresh();

    expect($this->messages)->each(fn ($message) => $message->is_unread->toBeTrue());
});
