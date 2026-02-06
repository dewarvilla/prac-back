<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Fecha\IndexFechaRequest;
use App\Http\Requests\V1\Fecha\StoreFechaRequest;
use App\Http\Requests\V1\Fecha\UpdateFechaRequest;
use App\Http\Requests\V1\Fecha\BulkDeleteFechaRequest;
use App\Http\Resources\V1\Fecha\FechaResource;
use App\Http\Resources\V1\Fecha\FechaCollection;
use App\Models\Fecha;
use App\Services\FechaService;

class FechaController extends Controller
{
    public function __construct(private FechaService $service)
    {
        $this->middleware('permission:fechas.view')->only(['index','show']);
        $this->middleware('permission:fechas.create')->only(['store']);
        $this->middleware('permission:fechas.edit')->only(['update']);
        $this->middleware('permission:fechas.delete')->only(['destroy','destroyBulk']);
    }

    public function index(IndexFechaRequest $request)
    {
        $perPage = (int) $request->query('per_page', 0);
        $filters = $request->validated();

        $result = $this->service->search($filters, $perPage, $request->query());

        return $perPage > 0
            ? new FechaCollection($result)
            : FechaResource::collection($result);
    }

    public function store(StoreFechaRequest $request)
    {
        $fecha = $this->service->create(
            $request->validated(),
            auth()->user(),
            $request->ip()
        );

        return (new FechaResource($fecha))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Fecha $fecha)
    {
        return new FechaResource($fecha);
    }

    public function update(UpdateFechaRequest $request, Fecha $fecha)
    {
        $updated = $this->service->update(
            $fecha->id,
            $request->validated(),
            auth()->user(),
            $request->ip()
        );
        abort_if(!$updated, 404);

        return new FechaResource($updated);
    }

    public function destroy(Fecha $fecha)
    {
        $ok = $this->service->delete($fecha->id);
        abort_if(!$ok, 404);

        return response()->noContent();
    }

    public function destroyBulk(BulkDeleteFechaRequest $request)
    {
        $ids = $request->validated()['ids'];
        $counts = $this->service->destroyBulk($ids);

        return response()->json([
            'ok'      => true,
            'message' => 'Fechas eliminadas correctamente.',
            'counts'  => $counts,
        ], 200);
    }
}
