<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    protected string $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
    protected ?string $serverKey;

    public function __construct()
    {
        $this->serverKey = env('FCM_SERVER_KEY');
    }

    /**
     * Send notification to multiple device tokens.
     *
     * @param array $tokens
     * @param string $title
     * @param string $message
     * @param array $payload
     * @return array
     */
    public function sendToTokens(array $tokens, string $title, string $message, array $payload = []): array
    {
        if (empty($tokens)) {
            return ['success' => 0, 'failure' => 0, 'results' => []];
        }

        $data = $this->buildPayload($title, $message, $payload);
        $data['registration_ids'] = $tokens;

        return $this->sendRequest($data);
    }

    /**
     * Send notification to a topic.
     *
     * @param string $topic
     * @param string $title
     * @param string $message
     * @param array $payload
     * @return array
     */
    public function sendToTopic(string $topic, string $title, string $message, array $payload = []): array
    {
        $data = $this->buildPayload($title, $message, $payload);
        $data['to'] = '/topics/' . $topic;

        return $this->sendRequest($data);
    }

    /**
     * Build the notification payload.
     *
     * @param string $title
     * @param string $message
     * @param array $customData
     * @return array
     */
    protected function buildPayload(string $title, string $message, array $customData = []): array
    {
        return [
            'notification' => [
                'title' => $title,
                'body' => $message,
                'sound' => 'default',
            ],
            'data' => $customData, // Custom data payload
            'priority' => 'high',
        ];
    }

    /**
     * Send the request to FCM.
     *
     * @param array $data
     * @return array
     */
    protected function sendRequest(array $data): array
    {
        if (!$this->serverKey) {
            Log::error('FCM Server Key is missing in .env');
            return ['error' => 'FCM Server Key is missing'];
        }

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $this->serverKey,
            'Content-Type' => 'application/json',
        ])->post($this->fcmUrl, $data);

        if ($response->failed()) {
            Log::error('FCM Send Error', ['response' => $response->body()]);
        }

        return $response->json();
    }
}
