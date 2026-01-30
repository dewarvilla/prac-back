<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auxilio\IndexAuxilioRequest;
use App\Http\Requests\V1\Auxilio\StoreAuxilioRequest;
use App\Http\Requests\V1\Auxilio\UpdateAuxilioRequest;
use App\Http\Requests\V1\Auxilio\BulkDeleteAuxilioRequest;
use App\Http\Resources\V1\Auxilio\AuxilioResource;
use App\Http\Resources\V1\Auxilio\AuxilioCollection;
use App\Models\Auxilio;
use App\Services\AuxilioService;

class AuxilioController extends Controller
{
    public function __construct(private AuxilioService $service)
    {
        $this->middleware('permission:auxilios.view')->only(['index','show']);
        $this->middleware('permission:auxilios.create')->only(['store']);
        $this->middleware('permission:auxilios.edit')->only(['update']);
        $this->middleware('permission:auxilios.delete')->only(['destroy','destroyBulk']);
    }

    public function index(IndexAuxilioRequest $request)
    {
        $perPage = (int) $request->query('per_page', 0);
        $filters = $request->validated();

        $result = $this->service->search($filters, $perPage);

        return $perPage > 0
            ? new AuxilioCollection($result)
            : AuxilioResource::collection($result);
    }

    public function store(StoreAuxilioRequest $request)
    {
        $auxilio = $this->service->create(
            $request->validated(),
            auth()->user(),
            $request->ip()
        );

        return (new AuxilioResource($auxilio))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Auxilio $auxilio)
    {
        return new AuxilioResource($auxilio);
    }

    public function update(UpdateAuxilioRequest $request, Auxilio $auxilio)
    {
        $updated = $this->service->update(
            $auxilio->id,
            $request->validated(),
            auth()->user(),
            $request->ip()
        );
        abort_if(!$updated, 404);

        return new AuxilioResource($updated);
    }

    public function destroy(Auxilio $auxilio)
    {
        $ok = $this->service->delete($auxilio->id);
        abort_if(!$ok, 404);

        return response()->noContent();
    }

    public function destroyBulk(BulkDeleteAuxilioRequest $request)
    {
        $ids = $request->validated()['ids'];
        $counts = $this->service->destroyBulk($ids);

        return response()->json([
            'ok'      => true,
            'message' => 'Auxilios eliminados correctamente.',
            'counts'  => $counts,
        ], 200);
    }
}
