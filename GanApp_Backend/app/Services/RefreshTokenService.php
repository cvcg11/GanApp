<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RefreshTokenService
{
    /**
     * Create a new class instance.
     */
   private string $cookieName = 'refresh_token';

    private function ttlDays(): int
    {
        return (int) env('REFRESH_TTL_DAYS', 14);
    }

    public function issue(User $user): string
    {
        $plain = rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '=');
        $hash  = hash('sha256', $plain);

        DB::table('refresh_tokens')->insert([
            'user_id'    => $user->id,
            'token_hash' => $hash,
            'client_ip'  => request()->ip(),
            'client_ua'  => Str::limit((string) request()->userAgent(), 255, ''),
            'expires_at' => now()->addDays($this->ttlDays()),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Setear cookie
        $minutes = 60 * $this->ttlDays();
        $domain  = config('session.domain');
        $secure  = (bool) config('session.secure', false);
        $same    = config('session.same_site', 'lax');
        $path    = config('session.path', '/');

        Cookie::queue(cookie(
            $this->cookieName, $plain, $minutes, $path, $domain, $secure, true, false, $same
        ));

        return $plain;
    }

    /**
     * Consume el refresh cookie (si válido), rota y devuelve el User.
     * Devuelve null si inválido.
     */
    public function rotateFromRequest(Request $req): ?User
    {
        $rt = $req->cookie($this->cookieName);
        if (!$rt) return null;

        $hash = hash('sha256', $rt);

        $row = DB::table('refresh_tokens')
            ->where('token_hash', $hash)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$row) return null;

        // comprobar atado a dispositivo
        $uaMatch = Str::limit((string) $req->userAgent(), 255, '') === $row->client_ua;
        $ipMatch = $req->ip() === $row->client_ip;

        if (!($uaMatch && $ipMatch)) {
            return null;
        }

        // invalidar el actual (one-time)
        DB::table('refresh_tokens')->where('id', $row->id)->update(['used_at' => now()]);

        // emitir y setear nuevo refresh
        $user = User::find($row->user_id);
        if (!$user) return null;

        $this->issue($user);

        return $user;
    }

    public function forget(): void
    {
        $domain = config('session.domain');
        $path   = config('session.path', '/');
        Cookie::queue(Cookie::forget($this->cookieName, $path, $domain));
    }
}
