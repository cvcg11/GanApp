<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class Handler extends ExceptionHandler
{
   protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->is('api/*')) {
            return response()->json([
                'status'  => 'error',
                'code'    => 401,
                'message' => 'Falta el token o es inválido.',
            ], 401);
        }

        // Para web, en el momento de que exista un login:
        // return redirect()->guest(route('login'));
        return parent::unauthenticated($request, $exception);
    }
}
