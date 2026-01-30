<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Legalizacion\IndexLegalizacionRequest;
use App\Http\Requests\V1\Legalizacion\StoreLegalizacionRequest;
use App\Http\Requests\V1\Legalizacion\UpdateLegalizacionRequest;
use App\Http\Requests\V1\Legalizacion\BulkDeleteLegalizacionRequest;
use App\Http\Resources\V1\Legalizacion\LegalizacionCollection;
use App\Http\Resources\V1\Legalizacion\LegalizacionResource;
use App\Models\Legalizacion;
use App\Services\LegalizacionService;

class LegalizacionController extends Controller
{
    public function __construct(private LegalizacionService $service)
    {
        $this->middleware('permission:legalizaciones.view')->only(['index','show']);
        $this->middleware('permission:legalizaciones.create')->only(['store']);
        $this->middleware('permission:legalizaciones.edit')->only(['update']);
        $this->middleware('permission:legalizaciones.delete')->only(['destroy','destroyBulk']);
    }

    public function index(IndexLegalizacionRequest $request)
    {
        $perPage = (int) $request->query('per_page', 0);
        $filters = $request->validated();

        $result = $this->service->search($filters, $perPage);

        return $perPage > 0
            ? new LegalizacionCollection($result)
            : LegalizacionResource::collection($result);
    }

    public function store(StoreLegalizacionRequest $request)
    {
        $legalizacion = $this->service->create(
            $request->validated(),
            auth()->user(),
            $request->ip()
        );

        return (new LegalizacionResource($legalizacion))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Legalizacion $legalizacion)
    {
        return new LegalizacionResource($legalizacion);
    }

    public function update(UpdateLegalizacionRequest $request, Legalizacion $legalizacion)
    {
        $updated = $this->service->update(
            $legalizacion->id,
            $request->validated(),
            auth()->user(),
            $request->ip()
        );
        abort_if(!$updated, 404);

        return new LegalizacionResource($updated);
    }

    public function destroy(Legalizacion $legalizacion)
    {
        $ok = $this->service->delete($legalizacion->id);
        abort_if(!$ok, 404);

        return response()->noContent();
    }

    public function destroyBulk(BulkDeleteLegalizacionRequest $request)
    {
        $ids = $request->validated()['ids'];
        $counts = $this->service->destroyBulk($ids);

        return response()->json([
            'ok'      => true,
            'message' => 'Legalizaciones eliminados correctamente.',
            'counts'  => $counts,
        ], 200);
    }
}
