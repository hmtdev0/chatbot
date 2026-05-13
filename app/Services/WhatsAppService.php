<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function sendMessage(string $to, string $text): void
    {
        $phoneNumberId = env('WHATSAPP_PHONE_NUMBER_ID');
        $token = env('WHATSAPP_ACCESS_TOKEN');

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'Content-Type' => 'application/json',
        ])->post("https://graph.facebook.com/v21.0/{$phoneNumberId}/messages", [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'body' => $text,
            ],
        ]);

        // 🔥 IMPORTANT DEBUG
        Log::info('WHATSAPP SEND RESPONSE', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);
    }
}
