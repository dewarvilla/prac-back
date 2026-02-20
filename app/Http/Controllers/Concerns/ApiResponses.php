<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

trait ApiResponses
{
    protected function ok(
        mixed $data = null,
        string $message = 'OK',
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        $payload = [
            'ok'      => true,
            'code'    => $status,
            'message' => $message,
        ];

        if ($data !== null) {
            if ($data instanceof JsonResource) {
                $payload['data'] = $data->resolve(request());

                if ($data->resource instanceof LengthAwarePaginator) {
                    $p = $data->resource;
                    $payload['meta'] = array_merge([
                        'current_page' => $p->currentPage(),
                        'per_page'     => $p->perPage(),
                        'total'        => $p->total(),
                        'last_page'    => $p->lastPage(),
                    ], $meta);
                } elseif (!empty($meta)) {
                    $payload['meta'] = $meta;
                }
            }
            elseif ($data instanceof LengthAwarePaginator) {
                $payload['data'] = $data->items();
                $payload['meta'] = array_merge([
                    'current_page' => $data->currentPage(),
                    'per_page'     => $data->perPage(),
                    'total'        => $data->total(),
                    'last_page'    => $data->lastPage(),
                ], $meta);
            }
            else {
                $payload['data'] = $data;

                if (!empty($meta)) {
                    $payload['meta'] = $meta;
                }
            }
        } elseif (!empty($meta)) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }
}
