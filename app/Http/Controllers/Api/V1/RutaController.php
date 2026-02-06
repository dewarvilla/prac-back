<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\IndexRutaRequest;
use App\Http\Requests\V1\StoreRutaRequest;
use App\Http\Requests\V1\UpdateRutaRequest;
use App\Http\Requests\V1\BulkDeleteRutaRequest;
use App\Http\Resources\V1\RutaCollection;
use App\Http\Resources\V1\RutaResource;
use App\Models\Ruta;
use App\Services\Ruta\RutaService;

class RutaController extends Controller
{
    public function __construct(private readonly RutaService $service)
    {
        $this->middleware('permission:rutas.view')->only(['index', 'show']);
        $this->middleware('permission:rutas.create')->only(['store']);
        $this->middleware('permission:rutas.edit')->only(['update']);
        $this->middleware('permission:rutas.delete')->only(['destroy', 'destroyBulk']);
    }

    public function index(IndexRutaRequest $request)
    {
        $perPage = (int) $request->query('per_page', 0);

        $result = $this->service->search(
            $request->validated(),
            $perPage
        );

        return $perPage > 0
            ? new RutaCollection($result)
            : RutaResource::collection($result);
    }

    public function store(StoreRutaRequest $request)
    {
        $ruta = $this->service->create(
            $request->validated(),
            $request->user(),
            $request->ip()
        );

        return (new RutaResource($ruta))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Ruta $ruta)
    {
        return new RutaResource($ruta);
    }

    public function update(UpdateRutaRequest $request, Ruta $ruta)
    {
        $updated = $this->service->update(
            $ruta->id,
            $request->validated(),
            $request->user(),
            $request->ip()
        );

        abort_if(!$updated, 404);

        return new RutaResource($updated);
    }

    public function destroy(Ruta $ruta)
    {
        $ok = $this->service->delete($ruta->id);
        abort_if(!$ok, 404);

        return response()->noContent();
    }

    public function destroyBulk(BulkDeleteRutaRequest $request)
    {
        $counts = $this->service->destroyBulk($request->validated()['ids']);

        return response()->json([
            'ok'      => true,
            'message' => 'Rutas eliminadas correctamente.',
            'counts'  => $counts,
        ], 200);
    }
}
