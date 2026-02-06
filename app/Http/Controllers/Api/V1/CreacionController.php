<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Creacion\IndexCreacionRequest;
use App\Http\Requests\V1\Creacion\StoreCreacionRequest;
use App\Http\Requests\V1\Creacion\UpdateCreacionRequest;
use App\Http\Requests\V1\Creacion\BulkDeleteCreacionRequest;
use App\Http\Resources\V1\Creacion\CreacionResource;
use App\Http\Resources\V1\Creacion\CreacionCollection;
use App\Models\Creacion;
use App\Services\CreacionService;

class CreacionController extends Controller
{
    public function __construct(private CreacionService $service)
    {
        $this->middleware('permission:creaciones.view')->only(['index','show']);
        $this->middleware('permission:creaciones.create')->only(['store']);
        $this->middleware('permission:creaciones.edit')->only(['update']);
        $this->middleware('permission:creaciones.delete')->only(['destroy','destroyBulk']);
    }

    public function index(IndexCreacionRequest $request)
    {
        $perPage = (int) $request->query('per_page', 0);
        $filters = $request->validated();

        $result = $this->service->search($filters, $perPage, $request->query());

        return $perPage > 0
            ? new CreacionCollection($result)
            : CreacionResource::collection($result);
    }

    public function store(StoreCreacionRequest $request)
    {
        $creacion = $this->service->create(
            $request->validated(),
            auth()->user(),
            $request->ip()
        );

        return (new CreacionResource($creacion))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Creacion $creacion)
    {
        return new CreacionResource($creacion->load('catalogo'));
    }

    public function update(UpdateCreacionRequest $request, Creacion $creacion)
    {
        $updated = $this->service->update(
            $creacion->id,
            $request->validated(),
            auth()->user(),
            $request->ip()
        );
        abort_if(!$updated, 404);

        return new CreacionResource($updated);
    }

    public function destroy(Creacion $creacion)
    {
        $ok = $this->service->delete($creacion->id);
        abort_if(!$ok, 404);

        return response()->noContent();
    }

    public function destroyBulk(BulkDeleteCreacionRequest $request)
    {
        $ids = $request->validated()['ids'];
        $counts = $this->service->destroyBulk($ids);

        return response()->json([
            'ok'      => true,
            'message' => 'Creaciones eliminadas correctamente.',
            'counts'  => $counts,
        ], 200);
    }
}
