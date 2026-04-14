<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a private WhatsApp message to a specific phone number.
     *
     * @param  string  $to  Phone number
     * @param  string  $text  Message content
     * @return Response|null
     */
    public function sendPrivateMessage(string $to, string $text)
    {
        // Format phone number to start with '62' assuming Indonesian numbers starting with '0'
        $cleanPhone = preg_replace('/^0/', '62', preg_replace('/\D/', '', $to));

        Log::info("WhatsAppService: Attempting PRIVATE message to {$cleanPhone}");

        $url = config('services.whatsapp.api_url').'/send-message';
        $payload = [
            'sessionId' => config('services.whatsapp.session_id'),
            'to' => $cleanPhone,
            'text' => $text,
        ];

        return $this->sendRequest($url, $payload);
    }

    /**
     * Send a broadcast message to the configured WhatsApp group.
     *
     * @param  string  $text  Message content
     * @return Response|null
     */
    public function sendGroupMessage(string $text)
    {
        $groupId = config('services.whatsapp.group_id');
        Log::info("WhatsAppService: Attempting GROUP message to {$groupId}");

        $url = config('services.whatsapp.api_url').'/send-group-message';
        $payload = [
            'sessionId' => config('services.whatsapp.session_id'),
            'to' => $groupId,
            'text' => $text,
        ];

        return $this->sendRequest($url, $payload);
    }

    /**
     * Execute the HTTP Request to the external WA API.
     *
     * @return Response|null
     */
    private function sendRequest(string $url, array $payload)
    {
        $startTime = microtime(true);
        Log::debug('WhatsAppService Request', ['url' => $url, 'payload' => $payload]);

        try {
            $response = Http::timeout(15) // Increased timeout slightly
                ->withHeaders([
                    'x-api-key' => config('services.whatsapp.api_key'),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($url, $payload);

            $duration = round(microtime(true) - $startTime, 3);

            if ($response->successful()) {
                Log::info("WhatsAppService: SUCCESS ({$duration}s) from {$url}");
            } else {
                Log::error("WhatsAppService: FAILED ({$duration}s) from {$url}", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return $response;
        } catch (\Exception $e) {
            $duration = round(microtime(true) - $startTime, 3);
            Log::error("WhatsAppService EXCEPTION ({$duration}s): ".$e->getMessage(), [
                'url' => $url,
                'payload' => $payload,
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }
}
