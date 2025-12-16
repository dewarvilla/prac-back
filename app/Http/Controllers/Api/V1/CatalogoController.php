<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Catalogo\IndexCatalogoRequest;
use App\Http\Requests\V1\Catalogo\StoreCatalogoRequest;
use App\Http\Requests\V1\Catalogo\UpdateCatalogoRequest;
use App\Http\Requests\V1\Catalogo\BulkCatalogoRequest;
use App\Http\Requests\V1\Catalogo\BulkDeleteCatalogoRequest;
use App\Http\Resources\V1\Catalogo\CatalogoResource;
use App\Http\Resources\V1\Catalogo\CatalogoCollection;
use App\Models\Catalogo;
use App\Services\CatalogoService;

class CatalogoController extends Controller
{
    public function __construct(private CatalogoService $service)
    {
        $this->middleware('permission:catalogos.view')->only(['index', 'show']);
        $this->middleware('permission:catalogos.create')->only(['store', 'storeBulk']);
        $this->middleware('permission:catalogos.edit')->only(['update']);
        $this->middleware('permission:catalogos.delete')->only(['destroy', 'destroyBulk']);
    }

    public function index(IndexCatalogoRequest $request)
    {
        $perPage = (int) $request->query('per_page', 0);
        $filters = $request->validated();
        $result = $this->service->search($filters, $perPage);

        return $perPage > 0
            ? new CatalogoCollection($result)                  
            : CatalogoResource::collection($result);           
    }

    public function store(StoreCatalogoRequest $request)
    {
        $catalogo = $this->service->create(
            $request->validated(),
            auth()->user(),
            $request->ip()
        );

        return (new CatalogoResource($catalogo))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Catalogo $catalogo)
    {
        return new CatalogoResource($catalogo);
    }

    public function update(UpdateCatalogoRequest $request, Catalogo $catalogo)
    {
        $updated = $this->service->update(
            $catalogo->id,
            $request->validated(),
            auth()->user(),
            $request->ip()
        );
        abort_if(!$updated, 404);

        return new CatalogoResource($updated);
    }

    public function destroy(Catalogo $catalogo)
    {
        $ok = $this->service->delete($catalogo->id);
        abort_if(!$ok, 404);

        return response()->noContent();
    }

    public function storeBulk(BulkCatalogoRequest $request)
    {
        $items = $request->validated()['items'] ?? [];
        $result = $this->service->storeBulk(
            $items,
            auth()->user(),
            $request->ip()
        );

        return CatalogoResource::collection($result['rows'])
            ->additional(['meta' => $result['meta']])
            ->response()
            ->setStatusCode(201);
    }

    public function destroyBulk(BulkDeleteCatalogoRequest $request)
    {
        $ids = $request->validated()['ids'];
        $counts = $this->service->destroyBulk($ids);

        return response()->json([
            'ok'      => true,
            'message' => 'Catálogos eliminados correctamente.',
            'counts'  => $counts,
        ], 200);
    }
}
