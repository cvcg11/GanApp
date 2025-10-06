<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpFoundation\Response;

class CatchAuthExceptionToJson
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         try {
            return $next($request);
        } catch (AuthenticationException|UnauthorizedHttpException $e) {
            // Respuesta JSON uniforme para rutas API cuando falta/expira el token
            return response()->json([
                'status'  => 'unauthenticated',
                'message' => 'Falta el token o es inválido.'
            ], 401);
        }
    }
}
