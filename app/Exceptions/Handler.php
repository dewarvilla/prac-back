<?php

namespace App\Exceptions;

use Throwable;
use PDOException;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Database\ConnectionException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    private const DB_UNIQUE_TO_API = [
        'approval_one_active' => [
            'status'     => 409,
            'error_code' => 'APPROVAL_ALREADY_ACTIVE',
            'message'    => 'Ya existe una aprobación activa para este recurso.',
        ],
    ];

    public function register(): void
    {
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'ok'         => false,
                    'code'       => 401,
                    'message'    => 'No autenticado.',
                    'error_code' => 'UNAUTHENTICATED',
                ], 401);
            }
        });

        $this->renderable(function (AuthorizationException $e, $request) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'ok'         => false,
                    'code'       => 403,
                    'message'    => 'No tienes permisos para esta acción.',
                    'error_code' => 'FORBIDDEN',
                ], 403);
            }
        });

        $this->renderable(function (ValidationException $e, $request) {
            if ($this->wantsJson($request)) {
                $errors = $e->errors();
                $first  = $this->firstValidationError($errors);

                return response()->json([
                    'ok'         => false,
                    'code'       => 422,
                    'message'    => 'No se pudo guardar. Revisa los campos marcados.',
                    'error_code' => 'VALIDATION_FAILED',
                    'errors'     => $errors,
                    'first_error'=> $first,
                ], 422);
            }
        });

        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'ok'         => false,
                    'code'       => 404,
                    'message'    => 'Recurso no encontrado.',
                    'error_code' => 'NOT_FOUND',
                ], 404);
            }
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'ok'         => false,
                    'code'       => 404,
                    'message'    => 'Ruta o recurso no encontrado.',
                    'error_code' => 'NOT_FOUND',
                ], 404);
            }
        });

        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'ok'         => false,
                    'code'       => 405,
                    'message'    => 'Método HTTP no permitido para esta ruta.',
                    'error_code' => 'METHOD_NOT_ALLOWED',
                ], 405);
            }
        });

        $this->renderable(function (ThrottleRequestsException $e, $request) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'ok'         => false,
                    'code'       => 429,
                    'message'    => 'Demasiadas solicitudes. Inténtalo más tarde.',
                    'error_code' => 'TOO_MANY_REQUESTS',
                ], 429);
            }
        });

        $this->renderable(function (ConnectionException|PDOException $e, $request) {
            if (! $this->wantsJson($request)) return;

            Log::error('DB connection error', [
                'message' => $e->getMessage(),
                'code'    => (string) $e->getCode(),
            ]);

            return response()->json([
                'ok'         => false,
                'code'       => 503,
                'message'    => 'En el momento no se puede realizar esta operación, inténtelo nuevamente. Si el error persiste comuníquese con el administrador dSe',
                'error_code' => 'DB_CONNECTION_ERROR',
                'details'    => [],
                'hint'       => app()->isLocal() ? $e->getMessage() : null,
            ], 503);
        });

        $this->renderable(function (QueryException $e, $request) {
            if (! $this->wantsJson($request)) return;

            $driver   = \DB::connection()->getDriverName();
            $sqlState = (string) $e->getCode();
            $errno    = (int) ($e->errorInfo[1] ?? 0);

            Log::error('SQL error', [
                'driver'   => $driver,
                'sqlstate' => $sqlState,
                'errno'    => $errno,
                'sql'      => method_exists($e, 'getSql') ? $e->getSql() : null,
                'bindings' => method_exists($e, 'getBindings') ? $e->getBindings() : null,
                'message'  => $e->getMessage(),
            ]);

            if ($driver === 'mysql' && in_array($errno, [2002, 2006, 2013, 1049], true)) {
                return response()->json([
                    'ok'         => false,
                    'code'       => 503,
                    'message'    => 'En el momento no se puede realizar esta operación, inténtelo nuevamente. Si el error persiste comuníquese con el administrador dSe',
                    'error_code' => 'DB_CONNECTION_ERROR',
                    'details'    => [],
                    'hint'       => app()->isLocal() ? $e->getMessage() : null,
                ], 503);
            }

            if ($driver === 'mysql' && $sqlState === '23000') {
                switch ($errno) {
                    case 1062: {
                        $det = $this->parseMysqlDuplicateKey($e);

                        $key = (string) ($det['key'] ?? '');
                        $keyNormalized = str_contains($key, '.')
                            ? substr($key, strrpos($key, '.') + 1)
                            : $key;

                        if ($keyNormalized !== '' && isset(self::DB_UNIQUE_TO_API[$keyNormalized])) {
                            $mapped = self::DB_UNIQUE_TO_API[$keyNormalized];

                            return response()->json([
                                'ok'         => false,
                                'code'       => (int) $mapped['status'],
                                'message'    => (string) $mapped['message'],
                                'error_code' => (string) $mapped['error_code'],
                                'details'    => [
                                    'constraint' => $keyNormalized,
                                    'entry'      => $det['entry'] ?? null,
                                ],
                                'hint'       => app()->isLocal() ? $e->getMessage() : null,
                            ], (int) $mapped['status']);
                        }

                        return response()->json([
                            'ok'         => false,
                            'code'       => 409,
                            'message'    => 'Ya existe un registro con esos datos (violación de restricción única).',
                            'error_code' => 'DB_DUPLICATE_KEY',
                            'details'    => [
                                'constraint' => $keyNormalized ?: ($det['key'] ?? null),
                                'entry'      => $det['entry'] ?? null,
                            ],
                            'hint'       => app()->isLocal() ? $e->getMessage() : null,
                        ], 409);
                    }

                    case 1451:
                        return response()->json([
                            'ok'         => false,
                            'code'       => 409,
                            'message'    => 'No se puede eliminar/actualizar: existen registros relacionados (restricción de clave foránea).',
                            'error_code' => 'DB_FOREIGN_KEY_RESTRICT',
                            'details'    => [],
                            'hint'       => app()->isLocal() ? $e->getMessage() : null,
                        ], 409);

                    case 1452:
                        return response()->json([
                            'ok'         => false,
                            'code'       => 409,
                            'message'    => 'No se puede guardar: referencia a un recurso inexistente (clave foránea inválida).',
                            'error_code' => 'DB_FOREIGN_KEY_INVALID',
                            'details'    => [],
                            'hint'       => app()->isLocal() ? $e->getMessage() : null,
                        ], 409);
                }

                return response()->json([
                    'ok'         => false,
                    'code'       => 409,
                    'message'    => 'Conflicto con el estado actual del recurso (restricción de integridad).',
                    'error_code' => 'DB_INTEGRITY_CONFLICT',
                    'details'    => [],
                    'hint'       => app()->isLocal() ? $e->getMessage() : null,
                ], 409);
            }

            if ($driver === 'pgsql') {
                if ($sqlState === '23505') {
                    return response()->json([
                        'ok'         => false,
                        'code'       => 409,
                        'message'    => 'Ya existe un registro con esos datos (violación de restricción única).',
                        'error_code' => 'DB_DUPLICATE_KEY',
                        'details'    => [],
                        'hint'       => app()->isLocal() ? $e->getMessage() : null,
                    ], 409);
                }
                if ($sqlState === '23503') {
                    return response()->json([
                        'ok'         => false,
                        'code'       => 409,
                        'message'    => 'Operación no permitida por clave foránea (registro relacionado).',
                        'error_code' => 'DB_FOREIGN_KEY_RESTRICT',
                        'details'    => [],
                        'hint'       => app()->isLocal() ? $e->getMessage() : null,
                    ], 409);
                }

                if (in_array($sqlState, ['08006','08001'], true)) {
                    return response()->json([
                        'ok'         => false,
                        'code'       => 503,
                        'message'    => 'En el momento no se puede realizar esta operación, inténtelo nuevamente. Si el error persiste comuníquese con el administrador dSe',
                        'error_code' => 'DB_CONNECTION_ERROR',
                        'details'    => [],
                        'hint'       => app()->isLocal() ? $e->getMessage() : null,
                    ], 503);
                }
            }

            return response()->json([
                'ok'         => false,
                'code'       => 500,
                'message'    => 'En el momento no se puede realizar esta operación, comuníquese con el administrador',
                'error_code' => 'SQL_ERROR',
                'details'    => [],
                'hint'       => app()->isLocal() ? $e->getMessage() : null,
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
                $status = $e->getStatusCode();

                return response()->json([
                    'ok'         => false,
                    'code'       => $status,
                    'message'    => $e->getMessage() ?: 'Error en la solicitud.',
                    'error_code' => 'HTTP_EXCEPTION',
                ], $status, $e->getHeaders());
            }
        });

        $this->renderable(function (Throwable $e, $request) {
            if (! $this->wantsJson($request)) return;

            Log::error('Unhandled exception', [
                'message' => $e->getMessage(),
                'class'   => get_class($e),
            ]);

            return response()->json([
                'ok'         => false,
                'code'       => 500,
                'message'    => 'Ocurrió un error inesperado.',
                'error_code' => 'UNEXPECTED_ERROR',
                'hint'       => app()->isLocal() ? $e->getMessage() : null,
            ], 500);
        });

        $this->reportable(function (Throwable $e) {
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

    private function firstValidationError(array $errors): ?array
    {
        foreach ($errors as $field => $messages) {
            if (is_array($messages) && count($messages) > 0) {
                return ['field' => (string) $field, 'message' => (string) $messages[0]];
            }
            if (is_string($messages) && $messages !== '') {
                return ['field' => (string) $field, 'message' => $messages];
            }
        }
        return null;
    }
}
