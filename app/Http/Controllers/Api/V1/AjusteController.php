<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Ajuste\IndexAjusteRequest;
use App\Http\Requests\V1\Ajuste\StoreAjusteRequest;
use App\Http\Requests\V1\Ajuste\UpdateAjusteRequest;
use App\Http\Requests\V1\Ajuste\BulkDeleteAjusteRequest;
use App\Http\Resources\V1\Ajuste\AjusteCollection;
use App\Http\Resources\V1\Ajuste\AjusteResource;
use App\Models\Ajuste;
use App\Services\AjusteService;

class AjusteController extends Controller
{
    public function __construct(private AjusteService $service)
    {
        $this->middleware('permission:ajustes.view')->only(['index','show']);
        $this->middleware('permission:ajustes.create')->only(['store']);
        $this->middleware('permission:ajustes.edit')->only(['update']);
        $this->middleware('permission:ajustes.delete')->only(['destroy','destroyBulk']);
    }

    public function index(IndexAjusteRequest $request)
    {
        $perPage  = (int) $request->query('per_page', 0);
        $filters  = $request->validated();

        $result = $this->service->search($filters, $perPage);

        return $perPage > 0
            ? new AjusteCollection($result)
            : AjusteResource::collection($result);
    }

    public function store(StoreAjusteRequest $request)
    {
        $ajuste = $this->service->create(
            $request->validated(),
            auth()->user(),
            $request->ip()
        );

        return (new AjusteResource($ajuste))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Ajuste $ajuste)
    {
        return new AjusteResource($ajuste);
    }

    public function update(UpdateAjusteRequest $request, Ajuste $ajuste)
    {
        $updated = $this->service->update(
            $ajuste->id,
            $request->validated(),
            auth()->user(),
            $request->ip()
        );
        abort_if(!$updated, 404);

        return new AjusteResource($updated);
    }

    public function destroy(Ajuste $ajuste)
    {
        $ok = $this->service->delete($ajuste->id);
        abort_if(!$ok, 404);

        return response()->noContent();
    }

    public function destroyBulk(BulkDeleteAjusteRequest $request)
    {
        $ids = $request->validated()['ids'];
        $counts = $this->service->destroyBulk($ids);

        return response()->json([
            'ok'      => true,
            'message' => 'Ajustes eliminados correctamente.',
            'counts'  => $counts,
        ], 200);
    }
}
