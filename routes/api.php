<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsAppWebhookController;

Route::get('/webhook', [WhatsAppWebhookController::class, 'verify']);
Route::post('/webhook', [WhatsAppWebhookController::class, 'receive']);
