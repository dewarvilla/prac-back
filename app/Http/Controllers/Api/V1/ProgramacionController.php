<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Programacion\IndexProgramacionRequest;
use App\Http\Requests\V1\Programacion\StoreProgramacionRequest;
use App\Http\Requests\V1\Programacion\UpdateProgramacionRequest;
use App\Http\Requests\V1\Programacion\BulkDeleteProgramacionRequest;
use App\Http\Resources\V1\Programacion\ProgramacionResource;
use App\Http\Resources\V1\Programacion\ProgramacionCollection;
use App\Models\Programacion;
use App\Services\ProgramacionService;
use Illuminate\Support\Facades\DB;

class ProgramacionController extends Controller
{
    public function __construct(private readonly ProgramacionService $service)
    {
        $this->middleware('permission:programaciones.view')->only(['index','show']);
        $this->middleware('permission:programaciones.create')->only(['store']);
        $this->middleware('permission:programaciones.edit')->only(['update']);
        $this->middleware('permission:programaciones.delete')->only(['destroy','destroyBulk']);
    }

    public function index(IndexProgramacionRequest $request)
    {
        $perPage = (int) $request->query('per_page', 0);
        $filters = $request->validated();

        $result = $this->service->search(
            $request->user(),
            $filters,
            $perPage,
            $request->query()
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
            $programacion,
            $request->validated(),
            $request->user(),
            $request->ip()
        );

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
        $ids = $request->validated()['ids'];

        return DB::transaction(function () use ($ids, $request) {
            $programaciones = Programacion::whereIn('id', $ids)->get();

            foreach ($programaciones as $p) {
                $this->authorize('delete', $p);
            }

            $counts = $this->service->destroyBulk($programaciones->pluck('id')->map(fn($x)=>(string)$x)->all());

            return response()->json([
                'ok'      => true,
                'message' => 'Programaciones eliminadas correctamente.',
                'counts'  => $counts,
            ], 200);
        });
    }
}
