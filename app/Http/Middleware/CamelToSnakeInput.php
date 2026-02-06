<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CamelToSnakeInput
{
    private function snakeKeys(mixed $data): mixed
    {
        // No tocar archivos subidos
        if ($data instanceof UploadedFile) {
            return $data;
        }

        // Arrays: convertir keys recursivamente
        if (is_array($data)) {
            $out = [];
            foreach ($data as $key => $value) {
                // Si es string => snake_case; si es numérica => se deja
                $newKey = is_string($key) ? Str::snake($key) : $key;
                $out[$newKey] = $this->snakeKeys($value);
            }
            return $out;
        }

        // Tipos escalares/objetos: no tocar
        return $data;
    }

    public function handle(Request $request, Closure $next)
    {
        try {
            // Querystring (?programaAcademico.lk=... etc.)
            if (!empty($request->query())) {
                $request->query->replace($this->snakeKeys($request->query()));
            }

            // Body JSON o form-data
            $contentType = (string) $request->headers->get('content-type', '');

            if (Str::contains($contentType, 'application/json')) {
                $raw = $request->getContent();

                if ($raw !== '') {
                    $json = json_decode($raw, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        logger()->warning('CamelToSnakeInput - JSON inválido', [
                            'path'         => $request->path(),
                            'content_type' => $contentType,
                            'json_error'   => json_last_error_msg(),
                        ]);
                    } elseif (is_array($json)) {
                        $snake = $this->snakeKeys($json);

                        // Reemplaza inputs y el bag JSON
                        $request->replace($snake);
                        $request->json()->replace($snake);
                    }
                }
            } else {
                $all = $request->all();
                if (!empty($all)) {
                    $snake = $this->snakeKeys($all);
                    $request->replace($snake);
                }
            }
        } catch (\Throwable $e) {
            logger()->error('CamelToSnakeInput middleware failed', [
                'path'    => $request->path(),
                'message' => $e->getMessage(),
            ]);
        }

        return $next($request);
    }
}
