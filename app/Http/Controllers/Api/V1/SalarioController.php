<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Salario\IndexSalarioRequest;
use App\Http\Requests\V1\Salario\StoreSalarioRequest;
use App\Http\Requests\V1\Salario\UpdateSalarioRequest;
use App\Http\Requests\V1\Salario\BulkDeleteSalarioRequest;
use App\Http\Resources\V1\Salario\SalarioResource;
use App\Http\Resources\V1\Salario\SalarioCollection;
use App\Models\Salario;
use App\Services\SalarioService;

class SalarioController extends Controller
{
    public function __construct(private readonly SalarioService $service)
    {
        $this->middleware('permission:salarios.view')->only(['index','show']);
        $this->middleware('permission:salarios.create')->only(['store']);
        $this->middleware('permission:salarios.edit')->only(['update']);
        $this->middleware('permission:salarios.delete')->only(['destroy','destroyBulk']);
    }

    public function index(IndexSalarioRequest $request)
    {
        $perPage = (int) $request->query('per_page', 0);
        $filters = $request->validated();

        $result = $this->service->search($filters, $perPage, $request->query());

        return $perPage > 0
            ? new SalarioCollection($result)
            : SalarioResource::collection($result);
    }

    public function store(StoreSalarioRequest $request)
    {
        $salario = $this->service->create(
            $request->validated(),
            auth()->user(),
            $request->ip()
        );

        return (new SalarioResource($salario))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Salario $salario)
    {
        return new SalarioResource($salario);
    }

    public function update(UpdateSalarioRequest $request, Salario $salario)
    {
        $updated = $this->service->update(
            $salario->id,
            $request->validated(),
            auth()->user(),
            $request->ip()
        );
        abort_if(!$updated, 404);

        return new SalarioResource($updated);
    }

    public function destroy(Salario $salario)
    {
        $ok = $this->service->delete($salario->id);
        abort_if(!$ok, 404);

        return response()->noContent();
    }

    public function destroyBulk(BulkDeleteSalarioRequest $request)
    {
        $ids = $request->validated()['ids'];
        $counts = $this->service->destroyBulk($ids);

        return response()->json([
            'ok'      => true,
            'message' => 'Salarios eliminados correctamente.',
            'counts'  => $counts,
        ], 200);
    }
}