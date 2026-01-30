<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Programacion\ApproveProgramacionRequest;
use App\Http\Requests\V1\Programacion\RejectProgramacionRequest;
use App\Models\Programacion;
use App\Services\ProgramacionApprovalService;

class ProgramacionApprovalController extends Controller
{
    public function __construct(private readonly ProgramacionApprovalService $service)
    {
        $this->middleware('permission:programaciones.aprobar.departamento')->only('approveDepartamento');
        $this->middleware('permission:programaciones.rechazar.departamento')->only('rejectDepartamento');

        $this->middleware('permission:programaciones.aprobar.postgrados')->only('approvePostgrados');
        $this->middleware('permission:programaciones.rechazar.postgrados')->only('rejectPostgrados');

        $this->middleware('permission:programaciones.aprobar.decano')->only('approveDecano');
        $this->middleware('permission:programaciones.rechazar.decano')->only('rejectDecano');

        $this->middleware('permission:programaciones.aprobar.jefe_postgrados')->only('approveJefePostgrados');
        $this->middleware('permission:programaciones.rechazar.jefe_postgrados')->only('rejectJefePostgrados');

        $this->middleware('permission:programaciones.aprobar.vicerrectoria')->only('approveVicerrectoria');
        $this->middleware('permission:programaciones.rechazar.vicerrectoria')->only('rejectVicerrectoria');
    }

    // ===== Aprobaciones =====
    public function approveDepartamento(ApproveProgramacionRequest $request, Programacion $programacion)
    {
        $data = $this->service->approve($programacion->id, 'depart', auth()->user(), $request->ip());
        return response()->json(['ok' => true, 'data' => $data], 200);
    }

    public function approvePostgrados(ApproveProgramacionRequest $request, Programacion $programacion)
    {
        $data = $this->service->approve($programacion->id, 'postg', auth()->user(), $request->ip());
        return response()->json(['ok' => true, 'data' => $data], 200);
    }

    public function approveDecano(ApproveProgramacionRequest $request, Programacion $programacion)
    {
        $data = $this->service->approve($programacion->id, 'decano', auth()->user(), $request->ip());
        return response()->json(['ok' => true, 'data' => $data], 200);
    }

    public function approveJefePostgrados(ApproveProgramacionRequest $request, Programacion $programacion)
    {
        $data = $this->service->approve($programacion->id, 'jefe_postg', auth()->user(), $request->ip());
        return response()->json(['ok' => true, 'data' => $data], 200);
    }

    public function approveVicerrectoria(ApproveProgramacionRequest $request, Programacion $programacion)
    {
        $data = $this->service->approve($programacion->id, 'vice', auth()->user(), $request->ip());
        return response()->json(['ok' => true, 'data' => $data], 200);
    }

    // ===== Rechazos =====
    public function rejectDepartamento(RejectProgramacionRequest $request, Programacion $programacion)
    {
        $data = $this->service->reject(
            $programacion->id,
            'depart',
            (string) $request->validated()['justificacion'],
            auth()->user(),
            $request->ip()
        );

        return response()->json(['ok' => true, 'data' => $data], 200);
    }

    public function rejectPostgrados(RejectProgramacionRequest $request, Programacion $programacion)
    {
        $data = $this->service->reject(
            $programacion->id,
            'postg',
            (string) $request->validated()['justificacion'],
            auth()->user(),
            $request->ip()
        );

        return response()->json(['ok' => true, 'data' => $data], 200);
    }

    public function rejectDecano(RejectProgramacionRequest $request, Programacion $programacion)
    {
        $data = $this->service->reject(
            $programacion->id,
            'decano',
            (string) $request->validated()['justificacion'],
            auth()->user(),
            $request->ip()
        );

        return response()->json(['ok' => true, 'data' => $data], 200);
    }

    public function rejectJefePostgrados(RejectProgramacionRequest $request, Programacion $programacion)
    {
        $data = $this->service->reject(
            $programacion->id,
            'jefe_postg',
            (string) $request->validated()['justificacion'],
            auth()->user(),
            $request->ip()
        );

        return response()->json(['ok' => true, 'data' => $data], 200);
    }

    public function rejectVicerrectoria(RejectProgramacionRequest $request, Programacion $programacion)
    {
        $data = $this->service->reject(
            $programacion->id,
            'vice',
            (string) $request->validated()['justificacion'],
            auth()->user(),
            $request->ip()
        );

        return response()->json(['ok' => true, 'data' => $data], 200);
    }
}
