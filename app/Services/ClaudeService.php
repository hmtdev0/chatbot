<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeService
{
    public function ask(string $message): string
    {
        // 🧠 Business knowledge
        $businessInfo = '
            Business Name: Sysbi Technologies Limited
            Location: Rawalpindi, Pakistan
            Opening Hours: Monday to Saturday, 9AM to 6PM

            Services:
            - Web Development
            - AI Chatbots
            - Web Designing
            - Mobile Apps

            Website: https://google.com

            IMPORTANT RULES:
            - Do NOT use markdown
            - Use simple text only
            - Use "-" instead of bullets
            - Keep response clean and short

            Instructions:
            - If user asks about hours, location, services → use ONLY this info
            - If not related → answer normally but briefly
            - Keep responses WhatsApp friendly
            ';

        $response = Http::withHeaders([
            'x-api-key' => env('CLAUDE_API_KEY'),
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [

            'model' => 'claude-opus-4-7',

            'max_tokens' => 500,

            'messages' => [
                [
                    'role' => 'user',

                    'content' => "
                    You are a professional WhatsApp business assistant.

                    $businessInfo

                    User Message:
                    $message
                    ",
                ],
            ],
        ]);

        // 🔥 Debug log
        Log::info('CLAUDE RAW RESPONSE', $response->json());

        if (! $response->successful()) {
            Log::error('Claude API failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return 'Sorry, AI service is currently unavailable.';
        }

        return $response->json('content.0.text')
            ?? 'No response from AI.';
    }
}
