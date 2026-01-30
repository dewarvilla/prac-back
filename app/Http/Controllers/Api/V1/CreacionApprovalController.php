<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Creacion\ApproveCreacionRequest;
use App\Http\Requests\V1\Creacion\RejectCreacionRequest;
use App\Models\Creacion;
use App\Services\CreacionApprovalService;

class CreacionApprovalController extends Controller
{
    public function __construct(private readonly CreacionApprovalService $service)
    {
        $this->middleware('permission:creaciones.aprobar.comite_acreditacion')->only('approveComiteAcreditacion');
        $this->middleware('permission:creaciones.rechazar.comite_acreditacion')->only('rejectComiteAcreditacion');

        $this->middleware('permission:creaciones.aprobar.consejo_facultad')->only('approveConsejoFacultad');
        $this->middleware('permission:creaciones.rechazar.consejo_facultad')->only('rejectConsejoFacultad');

        $this->middleware('permission:creaciones.aprobar.consejo_academico')->only('approveConsejoAcademico');
        $this->middleware('permission:creaciones.rechazar.consejo_academico')->only('rejectConsejoAcademico');
    }

    // ===== Aprobaciones =====
    public function approveComiteAcreditacion(ApproveCreacionRequest $request, Creacion $creacion)
    {
        $data = $this->service->approve(
            $creacion->id,
            'comite_acreditacion',
            auth()->user(),
            $request->ip()
        );

        return response()->json(['ok' => true, 'data' => $data], 200);
    }

    public function approveConsejoFacultad(ApproveCreacionRequest $request, Creacion $creacion)
    {
        $data = $this->service->approve(
            $creacion->id,
            'consejo_facultad',
            auth()->user(),
            $request->ip()
        );

        return response()->json(['ok' => true, 'data' => $data], 200);
    }

    public function approveConsejoAcademico(ApproveCreacionRequest $request, Creacion $creacion)
    {
        $data = $this->service->approve(
            $creacion->id,
            'consejo_academico',
            auth()->user(),
            $request->ip()
        );

        return response()->json(['ok' => true, 'data' => $data], 200);
    }

    // ===== Rechazos =====
    public function rejectComiteAcreditacion(RejectCreacionRequest $request, Creacion $creacion)
    {
        $data = $this->service->reject(
            $creacion->id,
            'comite_acreditacion',
            (string) $request->validated()['justificacion'],
            auth()->user(),
            $request->ip()
        );

        return response()->json(['ok' => true, 'data' => $data], 200);
    }

    public function rejectConsejoFacultad(RejectCreacionRequest $request, Creacion $creacion)
    {
        $data = $this->service->reject(
            $creacion->id,
            'consejo_facultad',
            (string) $request->validated()['justificacion'],
            auth()->user(),
            $request->ip()
        );

        return response()->json(['ok' => true, 'data' => $data], 200);
    }

    public function rejectConsejoAcademico(RejectCreacionRequest $request, Creacion $creacion)
    {
        $data = $this->service->reject(
            $creacion->id,
            'consejo_academico',
            (string) $request->validated()['justificacion'],
            auth()->user(),
            $request->ip()
        );

        return response()->json(['ok' => true, 'data' => $data], 200);
    }
}
