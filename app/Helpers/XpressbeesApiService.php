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
     * Get bearer token from cache or generate a new one
     *
     * @return string
     * @throws Exception
     */
    public static function getBearerToken()
    {
        if (Cache::has(self::$tokenCacheKey)) {
            return Cache::get(self::$tokenCacheKey);
        }

        try {
            $url = 'https://userauthapis.xbees.in/api/auth/generateToken';

            $headers = [
                'Content-Type' => 'application/json',
                'XBKey'        => 'Ehdua38479Bgasy',  // Your XBKey
                'AuthType'     => 'New',
            ];

            $payload = [
                'username'  => 'admin@hg500g.com',  // Your username
                'password'  => 'Xpress@1234567',    // Your password
                'secretkey' => 'a7d2fc1cbc0c5a5c4c009c51032ca6874e26acd460d22b49e920b11b7d67784c', // Your secret key
            ];

            $response = Http::withHeaders($headers)->post($url, $payload);

            Log::info('XpressBees Token API Response:', ['body' => $response->body()]);

            if (!$response->successful()) {
                throw new Exception('Failed to fetch token. Status: ' . $response->status() . ', Body: ' . $response->body());
            }

            $data = $response->json();

           $token = $data['token'] ?? null;


            if (!$token) {
                throw new Exception('Token not found in response: ' . json_encode($data));
            }

            // Cache for 39 minutes (token expires in 40 minutes)
            Cache::put(self::$tokenCacheKey, $token, now()->addMinutes(39));

            return $token;

        } catch (Exception $e) {
            Log::error('XpressBees Token Generation Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send Forward Manifest API request to XpressBees
     *
     * @param array $payload
     * @return array
     */
    public static function sendForwardManifest(array $payload)
    {
        try {
            $token = self::getBearerToken();

            if (!$token) {
                throw new Exception('Token not found.');
            }

            $headers = [
                'token' => $token,
                'versionnumber' => 'v1',
                'XBKey' => 'Ehdua38479Bgasy',
                'Content-Type' => 'application/json',
            ];

            $url = 'https://apishipmentmanifestation.xbees.in/shipmentmanifestation/forward';

            $response = Http::withHeaders($headers)
                            ->timeout(30)
                            ->post($url, $payload);

            if (!$response->successful()) {
                throw new Exception('Forward Manifest API failed. Status: ' . $response->status() . ', Body: ' . $response->body());
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error('XpressBees Forward Manifest Error: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }
    public static function trackShipments(array $awbNumbers)
    {
        $results = [];

        try {
            $token = self::getBearerToken(); 

            foreach ($awbNumbers as $awb) {
                try {
                    $response = Http::withHeaders([
                        'Content-Type'  => 'application/json',
                        'token'         => $token,
                        'versionnumber' => 'v1',
                    ])->post('https://apishipmenttracking.xbees.in/GetShipmentAuditLog', [
                        'AWBNumber' => $awb
                    ]);

                    if ($response->successful()) {
                        $results[$awb] = $response->json();
                    } else {
                        $results[$awb] = [
                            'error' => 'Tracking API failed: ' . $response->body()
                        ];
                        Log::error("XpressBees tracking failed for AWB $awb: " . $response->body());
                    }
                } catch (\Exception $e) {
                    $results[$awb] = [
                        'error' => 'Exception: ' . $e->getMessage()
                    ];
                    Log::error("XpressBees tracking exception for AWB $awb: " . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            Log::error('XpressBees Tracking Batch Error', [
                'message' => $e->getMessage(),
                'awbs' => $awbNumbers
            ]);

            foreach ($awbNumbers as $awb) {
                $results[$awb] = ['error' => $e->getMessage()];
            }
        }

        return $results;
    }

}
