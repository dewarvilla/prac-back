<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'ok'      => false,
                    'code'    => 401,
                    'message' => 'No autenticado.',
                ], 401);
            }
        });

        $this->renderable(function (ValidationException $e, $request) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'ok'      => false,
                    'code'    => 422,
                    'message' => 'Los datos enviados son inválidos.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        $this->renderable(function (AuthorizationException $e, $request) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'ok'      => false,
                    'code'    => 403,
                    'message' => 'No tienes permisos para esta acción.',
                ], 403);
            }
        });

        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'ok'      => false,
                    'code'    => 404,
                    'message' => 'Recurso no encontrado.',
                ], 404);
            }
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'ok'      => false,
                    'code'    => 404,
                    'message' => 'Ruta o recurso no encontrado.',
                ], 404);
            }
        });

        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'ok'      => false,
                    'code'    => 405,
                    'message' => 'Método HTTP no permitido para esta ruta.',
                ], 405);
            }
        });

        $this->renderable(function (ThrottleRequestsException $e, $request) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'ok'      => false,
                    'code'    => 429,
                    'message' => 'Demasiadas solicitudes. Inténtalo más tarde.',
                ], 429);
            }
        });

        $this->renderable(function (QueryException $e, $request) {
            if (! $this->wantsJson($request)) return;

            $sqlState = $e->getCode();
            $driver   = \DB::connection()->getDriverName();
            $errno    = (int) ($e->errorInfo[1] ?? 0);

            if ($driver === 'mysql' && $sqlState === '23000') {
                switch ($errno) {
                    case 1062:
                        $det = $this->parseMysqlDuplicateKey($e);
                        return response()->json([
                            'ok'         => false,
                            'code'       => 409,
                            'message'    => 'Ya existe un registro con esos datos (violación de restricción única).',
                            'constraint' => $det['key'] ?? null,
                            'entry'      => $det['entry'] ?? null,
                            'hint'       => app()->isLocal() ? $e->getMessage() : null,
                        ], 409);

                    case 1451:
                        return response()->json([
                            'ok'      => false,
                            'code'    => 409,
                            'message' => 'No se puede eliminar/actualizar: existen registros relacionados (restricción de clave foránea).',
                            'hint'    => app()->isLocal() ? $e->getMessage() : null,
                        ], 409);

                    case 1452:
                        return response()->json([
                            'ok'      => false,
                            'code'    => 409,
                            'message' => 'No se puede guardar: referencia a un recurso inexistente (clave foránea inválida).',
                            'hint'    => app()->isLocal() ? $e->getMessage() : null,
                        ], 409);

                    case 1216:
                    case 1217:
                        return response()->json([
                            'ok'      => false,
                            'code'    => 409,
                            'message' => 'Operación no permitida por restricciones de integridad referencial.',
                            'hint'    => app()->isLocal() ? $e->getMessage() : null,
                        ], 409);
                }

                return response()->json([
                    'ok'      => false,
                    'code'    => 409,
                    'message' => 'Conflicto con el estado actual del recurso (restricción de integridad).',
                    'hint'    => app()->isLocal() ? $e->getMessage() : null,
                ], 409);
            }

            if ($driver === 'pgsql') {
                if ($sqlState === '23505') {
                    return response()->json([
                        'ok'      => false,
                        'code'    => 409,
                        'message' => 'Ya existe un registro con esos datos (violación de restricción única).',
                        'hint'    => app()->isLocal() ? $e->getMessage() : null,
                    ], 409);
                }
                if ($sqlState === '23503') {
                    return response()->json([
                        'ok'      => false,
                        'code'    => 409,
                        'message' => 'Operación no permitida por clave foránea (registro relacionado).',
                        'hint'    => app()->isLocal() ? $e->getMessage() : null,
                    ], 409);
                }
            }

            return response()->json([
                'ok'      => false,
                'code'    => 500,
                'message' => 'Error de base de datos.',
                'error'   => app()->isLocal() ? $e->getMessage() : null,
            ], 500);
        });

        $this->renderable(function (ApiException $e, $request) {
            if (! $this->wantsJson($request)) return;

            return response()->json([
                'ok'         => false,
                'code'       => $e->statusCode,
                'message'    => $e->getMessage(),
                'error_code' => $e->errorCode,
                'details'    => $e->details,
                'hint'       => app()->isLocal() ? $e->getMessage() : null,
            ], $e->statusCode);
        });

        $this->renderable(function (HttpExceptionInterface $e, $request) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'ok'      => false,
                    'code'    => $e->getStatusCode(),
                    'message' => $e->getMessage() ?: 'Error en la solicitud.',
                ], $e->getStatusCode(), $e->getHeaders());
            }
        });

        $this->reportable(function (Throwable $e) {
            // Sentry/Bugsnag etc.
        });
    }

    private function wantsJson($request): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }

    private function parseMysqlDuplicateKey(QueryException $e): array
    {
        $msg   = (string) $e->getMessage();
        $entry = null;
        $key   = null;

        if (preg_match("/Duplicate entry '(.+?)' for key '(.+?)'/", $msg, $m)) {
            $entry = $m[1] ?? null;
            $key   = $m[2] ?? null;
        } elseif (preg_match("/for key '(.+?)'/", $msg, $m)) {
            $key = $m[1] ?? null;
        }

        return compact('entry', 'key');
    }
}
