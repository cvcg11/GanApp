<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::authenticateAccessTokensUsing(function ($accessToken, $isValid) {
            if (!$isValid)
                return false;

            $ua = Str::limit((string) request()->userAgent(), 255, '');
            $sameUa = $accessToken->client_ua === $ua;
            $sameIp = $accessToken->client_ip === request()->ip();

            if (!($sameUa && $sameIp)) {
                return false;
            }

            // refresca último uso (opcional)
            $accessToken->forceFill(['last_used_at' => now()])->save();

            return true;
        });
    }
}
