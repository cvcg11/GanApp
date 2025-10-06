<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\TokenCookieService;
use App\Services\RefreshTokenService;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(User::latest()->get(), 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function register(Request $request, TokenCookieService $tokens, RefreshTokenService $refresh)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => $fields['password'],
        ]);

        $user->tokens()->delete();

        $token = $tokens->issue($user);
        $tokens->issue($user);
        $refresh->issue($user);

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response()->json($response, 201);
    }

    public function refresh(Request $request, TokenCookieService $access, RefreshTokenService $refresh)
    {
        $user = $refresh->rotateFromRequest($request);
        if (!$user) {
            return response()->json(['message' => 'Invalid refresh'], 401);
        }

        // emitir nuevo access y setear cookie de access
        $access->issue($user);

        return response()->json(['status' => 'refreshed'], 200);
    }

    public function login(Request $request, TokenCookieService $access, RefreshTokenService $refresh)
    {
        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $fields['email'])->first();
        if (!$user) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Si usas pepper en el mutador, aquí compara con pepper (ya lo tienes).
        // Si dejaste Hash::check directo: ajusta a tu flujo con pepper.
        if (
            !Hash::check(
                hash_hmac(
                    config('security.password_pepper_algo', 'sha256'),
                    $fields['password'],
                    (string) config('security.password_pepper_current')
                ),
                $user->password
            )
        ) {
            // fallback a pepper OLD (rotación)
            $old = (string) config('security.password_pepper_old');
            if (
                !$old || !Hash::check(hash_hmac(
                    config('security.password_pepper_algo', 'sha256'),
                    $fields['password'],
                    $old
                ), $user->password)
            ) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }
            // si validó con old → rehash con pepper actual (mutador)
            $user->password = $fields['password'];
            $user->save();
        }

        if (Hash::needsRehash($user->password)) {
            $user->password = $fields['password'];
            $user->save();
        }

        // Revoca PATs viejos (opcional, recomendado)
        $user->tokens()->delete();

        // Emite access+refresh y setea cookies
        $access->issue($user);
        $refresh->issue($user);

        return response()->json(['user' => $user], 200);
    }

    public function logout(Request $request, TokenCookieService $access, RefreshTokenService $refresh)
    {
        $request->user()?->currentAccessToken()?->delete();
        $access->forget();
        $refresh->forget();

        // invalidar refresh usado (si lo trae)
        if ($rt = $request->cookie('refresh_token')) {
            \DB::table('refresh_tokens')
                ->where('token_hash', hash('sha256', $rt))
                ->update(['used_at' => now()]);
        }

        return response()->json(['message' => 'Sesión cerrada'], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
