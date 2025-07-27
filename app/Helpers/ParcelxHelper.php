<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ParcelxHelper
{
    public static function sendRequest($url, $data)
    {
        try {
            $access_key = env('PARCELX_ACCESS_KEY');
            $secret_key = env('PARCELX_SECRET_KEY');
            $auth_token = base64_encode($access_key . ':' . $secret_key);

            $response = Http::withHeaders([
                'access-token' => $auth_token,
            ])->withOptions(['verify' => false])
                ->post($url, $data);

            Log::info('ParcelX API Response:', ['url' => $url, 'response' => $response->json()]);

            return $response;
        } catch (\Exception $e) {
            Log::error('ParcelX API Exception:', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public static function sendGETRequest($url)
    {
        try {
            $access_key = env('PARCELX_ACCESS_KEY');
            $secret_key = env('PARCELX_SECRET_KEY');
            $auth_token = base64_encode($access_key . ':' . $secret_key);

            $response = Http::withHeaders([
                'access-token' => $auth_token,
            ])->withOptions(['verify' => false])
                ->get($url);

            Log::info('ParcelX API Response:', ['url' => $url, 'response' => $response->json()]);

            return $response;
        } catch (\Exception $e) {
            Log::error('ParcelX API Exception:', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
