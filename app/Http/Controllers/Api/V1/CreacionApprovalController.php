<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Aprobacion\ApproveRequest;
use App\Http\Requests\V1\Aprobacion\RejectRequest;
use App\Models\Creacion;
use App\Services\AprobacionService;

class CreacionApprovalController extends Controller
{
    public function __construct(private readonly AprobacionService $service)
    {
        $this->middleware('auth:sanctum');
    }

    public function approve(ApproveRequest $request, Creacion $creacion)
    {
        $updated = $this->service->aprobar(
            aprobable: $creacion,
            actor: $request->user(),
            ip: $request->ip(),
            justificacion: $request->validated()['justificacion'] ?? null
        );

        return response()->json(['ok' => true, 'data' => $updated], 200);
    }

    public function reject(RejectRequest $request, Creacion $creacion)
    {
        $updated = $this->service->rechazar(
            aprobable: $creacion,
            actor: $request->user(),
            ip: $request->ip(),
            justificacion: $request->validated()['justificacion']
        );

        return response()->json(['ok' => true, 'data' => $updated], 200);
    }
}
