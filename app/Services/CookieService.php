<?php

namespace App\Services;

use Illuminate\Contracts\Cookie\Factory;
use Illuminate\Cookie\CookieJar;
use Illuminate\Foundation\Application;
use Symfony\Component\HttpFoundation\Cookie;

class CookieService
{
    public function getCookieForRefreshToken(string $refreshToken, int $countMinutesExpirationRefreshToken): Application|Factory|CookieJar|Cookie
    {
        return cookie(
            'refresh_token',
            $refreshToken,
            $countMinutesExpirationRefreshToken,
            '/api/v1/refresh',
            null,
            true, // Secure
            true, // HttpOnly
            false, // raw
            'None'
            // SameSite
        );
    }

    public function getCookieForRefreshTokenWithPartitioned(string $refreshToken, int $countMinutesExpirationRefreshToken): string
    {
        return sprintf(
            'refresh_token=%s; Path=/api/v1/refresh; Max-Age=%d; Secure; HttpOnly; SameSite=None; Partitioned',
            urlencode($refreshToken),
            $countMinutesExpirationRefreshToken * 60
        );
    }
}
