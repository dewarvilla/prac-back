<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Reprogramacion\ApproveReprogramacionRequest;
use App\Http\Requests\V1\Reprogramacion\RejectReprogramacionRequest;
use App\Models\Reprogramacion;
use App\Services\ReprogramacionApprovalService;

class ReprogramacionApprovalController extends Controller
{
    public function __construct(private readonly ReprogramacionApprovalService $service)
    {
        $this->middleware('permission:reprogramaciones.aprobar.vicerrectoria')->only('approveVicerrectoria');
        $this->middleware('permission:reprogramaciones.rechazar.vicerrectoria')->only('rejectVicerrectoria');
    }

    public function approveVicerrectoria(ApproveReprogramacionRequest $request, Reprogramacion $reprogramacion)
    {
        $data = $this->service->approve($reprogramacion->id, 'vice', auth()->user(), $request->ip());
        return response()->json(['ok' => true, 'data' => $data], 200);
    }

    public function rejectVicerrectoria(RejectReprogramacionRequest $request, Reprogramacion $reprogramacion)
    {
        $data = $this->service->reject(
            $reprogramacion->id,
            'vice',
            (string) $request->validated()['justificacion'],
            auth()->user(),
            $request->ip()
        );

        return response()->json(['ok' => true, 'data' => $data], 200);
    }
}
