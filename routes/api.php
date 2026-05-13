<?php

use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/webhook', [WhatsAppWebhookController::class, 'verify']);
Route::post('/webhook', [WhatsAppWebhookController::class, 'receive']);

// webhook link for me
// https://suety-ezequiel-tremblingly.ngrok-free.dev/api/webhook
