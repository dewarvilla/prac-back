<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ComputeRouteRequest;
use App\Services\RoutesComputeService;

class RoutesComputeController extends Controller
{
    public function __construct(private readonly RoutesComputeService $svc)
    {
        $this->middleware('permission:rutas.view')->only(['compute']);
    }

    public function compute(ComputeRouteRequest $request)
    {
        $origin = $request->validated('origin');
        $dest   = $request->validated('dest');
        $mode   = $request->validated('mode') ?? 'DRIVE';

        try {
            $metrics = $this->svc->computeMetrics([
                'origin' => $origin,
                'dest'   => $dest,
                'mode'   => $mode,
            ]);

            return response()->json([
                'distance_m' => $metrics?->distance_m,
                'duration_s' => $metrics?->duration_s,
                'polyline'   => $metrics?->polyline,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'distance_m' => null,
                'duration_s' => null,
                'polyline'   => null,
                'exception'  => $e->getMessage(),
            ], 500);
        }
    }
}
