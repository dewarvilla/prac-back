<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ApproveRequest;
use App\Http\Requests\V1\RejectRequest;
use App\Models\Programacion;
use App\Services\AprobacionService;

class ProgramacionApprovalController extends Controller
{
    public function __construct(private readonly AprobacionService $service)
    {
        $this->middleware('auth:sanctum');
    }

    public function approve(ApproveRequest $request, Programacion $programacion)
    {
        $updated = $this->service->aprobar(
            aprobable: $programacion,
            actor: $request->user(),
            ip: $request->ip(),
            justificacion: $request->validated()['justificacion'] ?? null
        );

        return response()->json(['ok' => true, 'data' => $updated], 200);
    }

    public function reject(RejectRequest $request, Programacion $programacion)
    {
        $updated = $this->service->rechazar(
            aprobable: $programacion,
            actor: $request->user(),
            ip: $request->ip(),
            justificacion: $request->validated()['justificacion']
        );

        return response()->json(['ok' => true, 'data' => $updated], 200);
    }
}
