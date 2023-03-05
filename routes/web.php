<?php

use FmTod\SmsCommunications\Http\Controllers\ContactsController;
use FmTod\SmsCommunications\Http\Controllers\ConversationController;
use FmTod\SmsCommunications\Http\Controllers\MessagesController;
use FmTod\SmsCommunications\Http\Middleware\HandleInertiaRequests;

Route::middleware(['web', 'auth', HandleInertiaRequests::class])->prefix('sms-communications')->group(function () {
    Route::resources(['conversations' => ConversationController::class]);
    Route::resources(['contacts' => ContactsController::class]);
    Route::resources(['messages' => MessagesController::class]);
    Route::post('messages/delete', [MessagesController::class, 'deleteMessages']);
    Route::post('messages/unread', [MessagesController::class, 'unreadMessages']);
    Route::post('messages/pin', [MessagesController::class, 'pinMessages']);
    Route::post('messages/unpin', [MessagesController::class, 'unpinMessages']);
});
