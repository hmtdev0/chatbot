<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeService
{
    public function ask(string $message): string
    {
        $documentService = new DocumentService;

        $documents = $documentService->readAllDocuments();

        // 🧠 Business knowledge
        $instructions = '
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

            'max_tokens' => 2000,

            'messages' => [
                [
                    'role' => 'user',

                    'content' => "
                    You are a professional WhatsApp business assistant.

                    Use the business info and uploaded documents below to answer user questions.

                    BUSINESS INFO:
                    $instructions

                    DOCUMENT CONTENT:
                    $documents

                    USER MESSAGE:
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
