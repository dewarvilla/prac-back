<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Reprogramacion\IndexReprogramacionRequest;
use App\Http\Requests\V1\Reprogramacion\StoreReprogramacionRequest;
use App\Http\Requests\V1\Reprogramacion\UpdateReprogramacionRequest;
use App\Http\Requests\V1\Reprogramacion\BulkDeleteReprogramacionRequest;
use App\Http\Resources\V1\Reprogramacion\ReprogramacionCollection;
use App\Http\Resources\V1\Reprogramacion\ReprogramacionResource;
use App\Models\Reprogramacion;
use App\Services\ReprogramacionService;

class ReprogramacionController extends Controller
{
    public function __construct(private ReprogramacionService $service)
    {
        $this->middleware('permission:reprogramaciones.view')->only(['index','show']);
        $this->middleware('permission:reprogramaciones.create')->only(['store']);
        $this->middleware('permission:reprogramaciones.edit')->only(['update']);
        $this->middleware('permission:reprogramaciones.delete')->only(['destroy','destroyBulk']);
    }

    public function index(IndexReprogramacionRequest $request)
    {
        $perPage = (int) $request->query('per_page', 0);

        $result = $this->service->search(
            $request->validated(),
            $perPage
        );

        return $perPage > 0
            ? new ReprogramacionCollection($result)
            : ReprogramacionResource::collection($result);
    }

    public function store(StoreReprogramacionRequest $request)
    {
        $reprogramacion = $this->service->create(
            $request->validated(),
            auth()->user(),
            $request->ip()
        );

        return (new ReprogramacionResource($reprogramacion))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Reprogramacion $reprogramacion)
    {
        return new ReprogramacionResource($reprogramacion);
    }

    public function update(UpdateReprogramacionRequest $request, Reprogramacion $reprogramacion)
    {
        $updated = $this->service->update(
            $reprogramacion->id,
            $request->validated(),
            auth()->user(),
            $request->ip()
        );

        abort_if(!$updated, 404);

        return new ReprogramacionResource($updated);
    }

    public function destroy(Reprogramacion $reprogramacion)
    {
        $ok = $this->service->delete($reprogramacion->id);
        abort_if(!$ok, 404);

        return response()->noContent();
    }

    public function destroyBulk(BulkDeleteReprogramacionRequest $request)
    {
        $counts = $this->service->destroyBulk(
            $request->validated()['ids']
        );

        return response()->json([
            'ok'      => true,
            'message' => 'Reprogramaciones eliminadas correctamente.',
            'counts'  => $counts,
        ], 200);
    }
}
