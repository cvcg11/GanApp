<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AttachBearerTokenFromCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!$request->headers->has('Authorization')){
            $token = $request->cookie('token');

            if(!empty($token)){
                $request->headers->set('Authorization', 'Bearer ' . $token);
            }

        }
        return $next($request);
    }
}
