<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Exceptions;

use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\AttachBearerTokenFromCookie;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'abilities' => CheckAbilities::class,             // requiere TODAS las abilities listadas
            'ability'   => CheckForAnyAbility::class,        // requiere AL MENOS UNA de las listadas
        ]);

        // APLICAR SOLO EN EL GRUPO API (no global para no afectar vistas)
        $middleware->group('api', [
            ForceJsonResponse::class,            // fuerza Accept: application/json
            AddQueuedCookiesToResponse::class,   // materializa Cookie::queue en Set-Cookie
            AttachBearerTokenFromCookie::class,  // mete Authorization: Bearer <token> desde cookie
            // puedes agregar aquí throttle:api si lo deseas con $middleware->alias(...)
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Helper para formatear JSON uniforme
        $json = function (int $code, string $message, array $extra = []) {
            $payload = array_merge([
                'status'  => 'error',
                'code'    => $code,
                'message' => $message,
            ], $extra);

            return response()->json($payload, $code);
        };

        // 401: no autenticado
        $exceptions->renderable(function (AuthenticationException $e, $request) use ($json) {
            if ($request->is('api/*')) {
                return $json(401, 'Falta el token o es inválido.');
            }
        });

        // 403: no autorizado
        $exceptions->renderable(function (AuthorizationException $e, $request) use ($json) {
            if ($request->is('api/*')) {
                return $json(403, 'No tienes permisos para esta acción.');
            }
        });

        // 404: modelo o ruta no encontrada
        $exceptions->renderable(function (ModelNotFoundException|NotFoundHttpException $e, $request) use ($json) {
            if ($request->is('api/*')) {
                return $json(404, 'Recurso no encontrado.');
            }
        });

        // 405: método HTTP no permitido
        $exceptions->renderable(function (MethodNotAllowedHttpException $e, $request) use ($json) {
            if ($request->is('api/*')) {
                return $json(405, 'Método HTTP no permitido para esta ruta.');
            }
        });

        // 422: validación
        $exceptions->renderable(function (ValidationException $e, $request) use ($json) {
            if ($request->is('api/*')) {
                return $json(422, 'Errores de validación.', [
                    'errors' => $e->errors(), // { campo: [mensajes] }
                ]);
            }
        });

        // 429: rate limiting
        $exceptions->renderable(function (ThrottleRequestsException $e, $request) use ($json) {
            if ($request->is('api/*')) {
                return $json(429, 'Has realizado demasiadas solicitudes. Intenta más tarde.');
            }
        });

        // Cualquier HttpException con código conocido (ej. 400, 415, etc.)
        $exceptions->renderable(function (HttpExceptionInterface $e, $request) use ($json) {
            if ($request->is('api/*')) {
                $status = $e->getStatusCode();
                $message = $e->getMessage() ?: 'Error';
                return $json($status, $message);
            }
        });

        // 500: fallback genérico
        $exceptions->renderable(function (Throwable $e, $request) use ($json) {
            if ($request->is('api/*')) {
                // Log completo para devops/observabilidad
                Log::error('Unhandled exception', [
                    'exception' => $e::class,
                    'message'   => $e->getMessage(),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                    'trace'     => collect($e->getTrace())->take(5)->all(),
                ]);

                $payload = app()->isProduction()
                    ? ['detail' => null]
                    : ['detail' => $e->getMessage()];

                return $json(500, 'Error interno del servidor.', $payload);
            }
        });
    })
    ->create();
