<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FcmService
{
    private string $projectId;
    private string $credentialsPath;

    public function __construct()
    {
        $this->credentialsPath = storage_path('app/firebase-credentials.json');
        $this->projectId = config('services.fcm.project_id', 'photoappqa');
    }

    /**
     * Send push notification to a specific user (all their devices)
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        $fcmTokens = DeviceToken::where('user_id', $user->id)
                                ->whereNotNull('fcm_token')
                                ->pluck('fcm_token')
                                ->toArray();

        foreach ($fcmTokens as $token) {
            $this->sendToToken($token, $title, $body, $data);
        }
    }

    /**
     * Send push notification to multiple users (all their devices)
     */
    public function sendToUsers(array $userIds, string $title, string $body, array $data = []): void
    {
        $fcmTokens = DeviceToken::whereIn('user_id', $userIds)
                                ->whereNotNull('fcm_token')
                                ->pluck('fcm_token')
                                ->toArray();

        foreach ($fcmTokens as $token) {
            $this->sendToToken($token, $title, $body, $data);
        }
    }

    /**
     * Send push notification to a single FCM token via HTTP v1 API
     */
    protected function sendToToken(string $fcmToken, string $title, string $body, array $data = []): void
    {
        $accessToken = $this->getAccessToken();

        if (empty($accessToken)) {
            Log::warning('FCM: Could not get access token. Skipping push notification.');
            return;
        }

        try {
            $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

            $message = [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'sound'            => 'default',
                            'channel_id'       => 'high_importance_channel',
                            'default_sound'    => true,
                        ],
                    ],
                    'data' => array_map('strval', $data), // FCM v1 requires string values
                ],
            ];

            $response = Http::withToken($accessToken)
                ->post($url, $message);

            if ($response->failed()) {
                $error = $response->json();
                Log::error('FCM send failed', [
                    'token'    => substr($fcmToken, 0, 20) . '...',
                    'status'   => $response->status(),
                    'error'    => $error['error']['message'] ?? $response->body(),
                ]);

                // Remove invalid token
                if ($response->status() === 404 || ($error['error']['status'] ?? '') === 'NOT_FOUND') {
                    DeviceToken::where('fcm_token', $fcmToken)->update(['fcm_token' => null]);
                    Log::info('FCM: Removed invalid token');
                }
            }
        } catch (\Exception $e) {
            Log::error('FCM exception: ' . $e->getMessage());
        }
    }

    /**
     * Get OAuth2 access token from service account credentials.
     * Cached for 55 minutes (tokens expire after 60 min).
     */
    private function getAccessToken(): ?string
    {
        return Cache::remember('fcm_access_token', 55 * 60, function () {
            if (!file_exists($this->credentialsPath)) {
                Log::error('FCM: firebase-credentials.json not found at ' . $this->credentialsPath);
                return null;
            }

            $credentials = json_decode(file_get_contents($this->credentialsPath), true);

            if (!$credentials) {
                Log::error('FCM: Invalid firebase-credentials.json');
                return null;
            }

            // Create JWT
            $now = time();
            $header = $this->base64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $payload = $this->base64url(json_encode([
                'iss'   => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ]));

            $unsigned = "$header.$payload";

            // Sign with private key
            $privateKey = openssl_pkey_get_private($credentials['private_key']);
            if (!$privateKey) {
                Log::error('FCM: Invalid private key in credentials');
                return null;
            }

            openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            $jwt = "$unsigned." . $this->base64url($signature);

            // Exchange JWT for access token
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('FCM: Token exchange failed', ['response' => $response->body()]);
            return null;
        });
    }

    /**
     * Base64url encode (no padding)
     */
    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
