<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class TokenCookieService
{
    /**
     * Create a new class instance.
     */
     private string $cookieName = 'token';

    public function issue(User $user): string
    {
        // Abilities mínimas; ajusta si necesitas scopes
        $new = $user->createToken('auth', ['read','write']);
        $plain = $new->plainTextToken;

        // Guardar IP/UA en el PAT
        $new->accessToken->forceFill([
            'client_ip'    => request()->ip(),
            'client_ua'    => Str::limit((string) request()->userAgent(), 255, ''),
            'last_used_at' => now(),
        ])->save();

        // Cookies según config de sesión
        $minutes = (int) (config('sanctum.expiration') ?? 60); // access corto
        $domain  = config('session.domain');
        $secure  = (bool) config('session.secure', false);
        $same    = config('session.same_site', 'lax');
        $path    = config('session.path', '/');

        Cookie::queue(cookie(
            $this->cookieName, $plain, $minutes, $path, $domain, $secure, true, false, $same
        ));

        return $plain;
    }

    public function forget(): void
    {
        $domain = config('session.domain');
        $path   = config('session.path', '/');
        Cookie::queue(Cookie::forget($this->cookieName, $path, $domain));
    }
}
