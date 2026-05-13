<?php

namespace App\Http\Controllers;

use App\Services\ClaudeService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Meta Webhook Verification
     */
    public function verify(Request $request)
    {
        Log::info('VERIFY HIT');
        Log::info($request->all());

        $verify_token = 'husnain';

        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        Log::info("Mode: $mode | Token: $token | Challenge: $challenge");

        if ($mode === 'subscribe' && $token === $verify_token) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Verification failed', 403);
    }

    /**
     * Incoming WhatsApp Messages
     */
    public function receive(Request $request)
    {
        $data = $request->all();

        $value = $data['entry'][0]['changes'][0]['value'] ?? [];

        // 1. ONLY process real messages
        if (! isset($value['messages'])) {
            Log::info('⛔ Ignored non-message webhook');

            return response()->json(['ok' => true]);
        }

        $message = $value['messages'][0];

        $from = $message['from'] ?? null;
        $text = $message['text']['body'] ?? '';
        $messageId = $message['id'] ?? null;

        // ✅ 2. PUT DEDUP CHECK HERE (RIGHT AFTER MESSAGE EXTRACTION)
        if (Cache::has("wa_msg_{$messageId}")) {
            Log::info("Duplicate message ignored: {$messageId}");

            return response()->json(['ok' => true]);
        }

        Cache::put(
            "wa_msg_{$messageId}",
            true,
            now()->addMinutes(10)
        );

        Log::info("User Message: $text");

        // 3. Claude call (ONLY ONCE)
        $claude = new ClaudeService;
        $reply = $claude->ask($text);

        // 4. Send WhatsApp reply
        $wa = new WhatsAppService;
        $wa->sendMessage($from, $reply);

        return response()->json(['ok' => true]);
    }
}
