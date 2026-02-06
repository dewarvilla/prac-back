<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ruta;
use App\Actions\Ruta\ComputeRutaMetricsAction;
use App\Actions\Ruta\SyncRutaPeajesAction;
use App\Actions\Ruta\ComputeTotalPeajesCategoriaAction;
use App\Http\Requests\V1\Ruta\ComputeRutaMetricsRequest;
use App\Http\Requests\V1\Ruta\SyncRutaPeajesRequest;
use App\Http\Requests\V1\Ruta\TotalCategoriaPeajesRequest;

class RutapeajesSyncController extends Controller
{
    public function __construct(
        private readonly ComputeRutaMetricsAction $computeAction,
        private readonly SyncRutaPeajesAction $syncAction,
        private readonly ComputeTotalPeajesCategoriaAction $totalAction,
    ) {
        $this->middleware('permission:rutas.edit')
            ->only(['computeMetrics', 'syncPeajes', 'totalCategoria']);
    }

    public function computeMetrics(ComputeRutaMetricsRequest $req, Ruta $ruta)
    {
        $mode = $req->validated('mode') ?? 'DRIVE';

        $ruta = $this->computeAction->execute($ruta, $mode);

        return response()->json([
            'ok'   => true,
            'data' => [
                'distancia_m' => $ruta->distancia_m,
                'duracion_s'  => $ruta->duracion_s,
                'polyline'    => $ruta->polyline,
            ],
        ]);
    }

    // POST /rutas/{ruta}/peajes/sync
    public function syncPeajes(SyncRutaPeajesRequest $req, Ruta $ruta)
    {
        if (!$ruta->polyline) {
            return response()->json([
                'ok' => false,
                'message' => 'La ruta no tiene polyline. Ejecute primero compute-metrics.',
            ], 400);
        }

        $categoria = $req->validated('categoria')
            ?? strtoupper(trim((string) ($ruta->categoria_vehiculo ?? 'I')));

        try {
            $res = $this->syncAction->execute($ruta, $categoria);

            return response()->json([
                'ok'      => true,
                'message' => "Peajes sincronizados correctamente (Categoría {$categoria}).",
                'data'    => [
                    'insertados'  => $res['insertados'] ?? 0,
                    'total_valor' => $res['total_valor'] ?? 0,
                    'num_peajes'  => $ruta->peajes()->count(),
                ],
            ]);
        } catch (\Throwable $e) {
            \Log::error("Error al sincronizar peajes: ".$e->getMessage());

            return response()->json([
                'ok' => false,
                'message' => 'Error al sincronizar peajes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // GET /rutas/{ruta}/peajes/total?cat=I
    public function totalCategoria(TotalCategoriaPeajesRequest $req, Ruta $ruta)
    {
        try {
            $cat = $req->validated('cat'); // null o I..VII

            $result = $this->totalAction->execute($ruta, $cat);

            if ($result['mode'] === 'all') {
                return response()->json([
                    'ok'      => true,
                    'cat'     => null,
                    'totales' => $result['totales'],
                ]);
            }

            return response()->json([
                'ok'    => true,
                'cat'   => $result['cat'],
                'total' => $result['total'],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'ok' => false,
                'code' => 422,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
