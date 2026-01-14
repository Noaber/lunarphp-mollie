<?php

use Noaber\Lunar\Mollie\Controllers\MollieRedirectController;
use Noaber\Lunar\Mollie\Controllers\MollieWebhookController;

Route::middleware('web')->group(function() {
    Route::get('mollie/redirect/{order}/{transaction}', [MollieRedirectController::class, 'redirect'])
        ->name('mollie.redirect');
});

Route::match(['GET', 'POST'], 'mollie/webhook', [MollieWebhookController::class, 'webhook'])
    ->name('mollie.webhook');
