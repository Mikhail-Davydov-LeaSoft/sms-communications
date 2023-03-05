<?php

use FmTod\SmsCommunications\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::prefix('v1/client')->group(function () {
    Route::any('webhooks/inbound-message/{service}', WebhookController::class)->whereIn('service', ['nexmo', 'brytecall', 'whatsapp']);
});
