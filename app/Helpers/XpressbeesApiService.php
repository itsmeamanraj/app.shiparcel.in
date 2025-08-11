<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XpressbeesApiService
{
    protected static $tokenCacheKey = 'xpressbees_api_token';

    /**
     * Get valid bearer token from cache or fetch a new one
     */
    public static function getBearerToken()
    {
        // If token exists in cache, return it
        if (Cache::has(self::$tokenCacheKey)) {
            return Cache::get(self::$tokenCacheKey);
        }

        // Request new token from XpressBees
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic YOUR_XPRESSBEES_BASIC_AUTH',
        ])->post('https://api.xpressbees.com/api/auth/token'); // Update URL if needed

        if (!$response->successful()) {
            throw new Exception('Failed to fetch token from XpressBees. Status: ' . $response->status());
        }

        $data = $response->json();
        $token = $data['data']['token'] ?? null;

        if (!$token) {
            throw new Exception('Token not found in XpressBees response.');
        }

        // Cache token with expiry buffer
        $expiresInSeconds = $data['data']['expiry'] ?? 2400;
        $cacheDurationInMinutes = floor($expiresInSeconds / 60) - 1;
        Cache::put(self::$tokenCacheKey, $token, now()->addMinutes($cacheDurationInMinutes));

        return $token;
    }

    /**
     * Call a protected XpressBees API
     */
    public static function callXpressbeesApi(string $endpoint, string $method = 'post', array $body = [])
    {
        $token = self::getBearerToken();

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ];

        $url = "https://api.xpressbees.com/api/{$endpoint}";

        $response = Http::withHeaders($headers)->{$method}($url, $body);

        if (!$response->successful()) {
            throw new Exception("XpressBees API call failed: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Generic sendRequest method like EkartApiService
     */
    public static function sendRequest($url, $data, $method = 'POST')
    {
        try {
            // Get fresh token
            $token = self::getBearerToken();

            $client = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ])->withOptions(['verify' => false]);

            $method = strtoupper($method);
            switch ($method) {
                case 'PUT':
                    $response = $client->put($url, $data);
                    break;
                case 'GET':
                    $response = $client->get($url, $data);
                    break;
                case 'DELETE':
                    $response = $client->delete($url, $data);
                    break;
                default:
                    $response = $client->post($url, $data);
                    break;
            }

            Log::info('XpressBees API Response:', [
                'url'      => $url,
                'method'   => $method,
                'response' => $response->json(),
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('XpressBees API Exception:', ['message' => $e->getMessage()]);

            return new \Illuminate\Http\Client\Response(
                new \GuzzleHttp\Psr7\Response(500, [], json_encode(['error' => $e->getMessage()])),
                new \Illuminate\Http\Request('POST', $url)
            );
        }
    }
}
