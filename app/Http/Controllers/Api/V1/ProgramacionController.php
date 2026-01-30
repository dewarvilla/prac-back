<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Programacion\IndexProgramacionRequest;
use App\Http\Requests\V1\Programacion\StoreProgramacionRequest;
use App\Http\Requests\V1\Programacion\UpdateProgramacionRequest;
use App\Http\Requests\V1\Programacion\BulkDeleteProgramacionRequest;
use App\Http\Resources\V1\Programacion\ProgramacionCollection;
use App\Http\Resources\V1\Programacion\ProgramacionResource;
use App\Models\Programacion;
use App\Services\ProgramacionService;

class ProgramacionController extends Controller
{
    public function __construct(private ProgramacionService $service)
    {
        $this->middleware('permission:programaciones.view')->only(['index','show']);
        $this->middleware('permission:programaciones.create')->only(['store']);
        $this->middleware('permission:programaciones.edit')->only(['update']);
        $this->middleware('permission:programaciones.delete')->only(['destroy','destroyBulk']);
    }

    public function index(IndexProgramacionRequest $request)
    {
        $perPage = (int) $request->query('per_page', 0);

        $result = $this->service->search(
            $request->validated(),
            $perPage,
            $request->user()
        );

        return $perPage > 0
            ? new ProgramacionCollection($result)
            : ProgramacionResource::collection($result);
    }

    public function store(StoreProgramacionRequest $request)
    {
        $this->authorize('create', Programacion::class);

        $programacion = $this->service->create(
            $request->validated(),
            $request->user(),
            $request->ip()
        );

        return (new ProgramacionResource($programacion))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Programacion $programacion)
    {
        $this->authorize('view', $programacion);
        return new ProgramacionResource($programacion);
    }

    public function update(UpdateProgramacionRequest $request, Programacion $programacion)
    {
        $this->authorize('update', $programacion);

        $updated = $this->service->update(
            $programacion->id,
            $request->validated(),
            $request->user(),
            $request->ip()
        );

        abort_if(!$updated, 404);
        return new ProgramacionResource($updated);
    }

    public function destroy(Programacion $programacion)
    {
        $this->authorize('delete', $programacion);

        $ok = $this->service->delete($programacion->id);
        abort_if(!$ok, 404);

        return response()->noContent();
    }

    public function destroyBulk(BulkDeleteProgramacionRequest $request)
    {
        $counts = $this->service->destroyBulk(
            $request->validated()['ids'],
            $request->user()
        );

        return response()->json([
            'ok'      => true,
            'message' => 'Programaciones eliminadas correctamente.',
            'counts'  => $counts,
        ], 200);
    }
}
