<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PddiktiTokenService
{
    const CACHE_KEY = 'pddikti_ws_token';

    public function getToken(): string
    {
        return Cache::remember(
            self::CACHE_KEY,
            config('pddikti.ttl'),
            fn () => $this->requestNewToken()
        );
    }

    public function refreshToken(): string
    {
        $token = $this->requestNewToken();
        Cache::put(self::CACHE_KEY, $token, config('pddikti.ttl'));

        return $token;
    }

    protected function requestNewToken(): string
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post(config('pddikti.url'), [
            'act' => 'GetToken',
            'username' => config('pddikti.username'),
            'password' => config('pddikti.password'),
        ]);

        if (! $response->successful()) {
            throw new Exception('Gagal menghubungi Web Service.');
        }

        $data = $response->json();

        if (($data['error_code'] ?? 1) !== 0) {
            throw new Exception('Error GetToken: '.($data['error_desc'] ?? 'Tidak diketahui'));
        }

        return $data['data']['token'] ?? throw new Exception('Token tidak ditemukan pada response');
    }
}
