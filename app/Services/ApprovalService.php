<?php

namespace App\Services;

use App\Exceptions\ApprovalException;
use App\Models\ApprovalRequest;
use App\Models\User;
use App\Repositories\Approval\ApprovalInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApprovalService
{
    public function __construct(private readonly ApprovalInterface $repo) {}

    public function start(Model $approvable, string $definitionCode, ?User $user, string $ip): ApprovalRequest
    {
        return DB::transaction(function () use ($approvable, $definitionCode, $user, $ip) {

            $def = $this->repo->findDefinitionByCode($definitionCode);

            if (!$def) {
                throw new ApprovalException(
                    message: 'Definición de aprobación no encontrada.',
                    statusCode: 404,
                    errorCode: 'APPROVAL_DEFINITION_NOT_FOUND',
                    details: ['code' => $definitionCode]
                );
            }

            if (!$def->is_active) {
                throw new ApprovalException(
                    message: 'El flujo de aprobación está inactivo.',
                    statusCode: 409,
                    errorCode: 'APPROVAL_DEFINITION_INACTIVE',
                    details: ['code' => $definitionCode]
                );
            }

            if ($def->steps->count() === 0) {
                throw new ApprovalException(
                    message: 'El flujo de aprobación no tiene pasos configurados.',
                    statusCode: 409,
                    errorCode: 'APPROVAL_DEFINITION_NO_STEPS',
                    details: ['code' => $definitionCode]
                );
            }

            $active = $this->repo->findActiveRequestFor($approvable);
            if ($active) {
                throw new ApprovalException(
                    message: 'Ya existe una solicitud de aprobación activa para este recurso.',
                    statusCode: 409,
                    errorCode: 'APPROVAL_ALREADY_ACTIVE',
                    details: [
                        'approval_request_id' => $active->id,
                        'approvable_type'     => $approvable::class,
                        'approvable_id'       => (string) $approvable->getKey(),
                    ]
                );
            }

            $uid = $user?->id ?? 0;
            $now = now();

            $req = $this->repo->createRequest([
                'approvable_type'        => $approvable::class,
                'approvable_id'          => (string) $approvable->getKey(),
                'approval_definition_id' => $def->id,
                'status'                 => 'pending',
                'current_step_order'     => 1,

                'active_key'             => 1,

                'fechacreacion'          => $now,
                'fechamodificacion'      => $now,
                'usuariocreacion'        => $uid,
                'usuariomodificacion'    => $uid,
                'ipcreacion'             => $ip,
                'ipmodificacion'         => $ip,
            ]);

            $rows = [];
            foreach ($def->steps as $s) {
                $rows[] = [
                    'id'                  => (string) Str::uuid(),
                    'approval_request_id' => $req->id,
                    'step_order'          => (int) $s->step_order,
                    'role_key'            => $s->role_key,
                    'status'              => 'pending',
                    'acted_by'            => null,
                    'acted_at'            => null,
                    'comment'             => null,

                    'fechacreacion'       => $now,
                    'fechamodificacion'   => $now,
                    'usuariocreacion'     => $uid,
                    'usuariomodificacion' => $uid,
                    'ipcreacion'          => $ip,
                    'ipmodificacion'      => $ip,
                ];
            }

            $this->repo->createStepsBulk($rows);

            return $req->fresh()->load(['definition','steps']);
        });
    }

    public function approve(string $approvalRequestId, User $user, ?string $comment, string $ip): ApprovalRequest
    {
        return DB::transaction(function () use ($approvalRequestId, $user, $comment, $ip) {

            $req = $this->repo->lockRequest($approvalRequestId);
            if (!$req) {
                throw new ApprovalException('Solicitud no encontrada.', 404, 'APPROVAL_REQUEST_NOT_FOUND');
            }

            if ((int)$req->active_key !== 1 || $req->status !== 'pending') {
                throw new ApprovalException(
                    'La solicitud no está pendiente o no está activa.',
                    409,
                    'APPROVAL_NOT_ACTIONABLE',
                    ['status' => $req->status, 'active_key' => $req->active_key]
                );
            }

            $req->load(['steps','definition.steps']);

            $current = $req->steps->firstWhere('step_order', (int)$req->current_step_order);
            if (!$current) {
                throw new ApprovalException('Paso actual no encontrado.', 409, 'APPROVAL_CURRENT_STEP_MISSING');
            }

            $this->assertCanAct($user, $req, 'aprobar', $current->role_key);

            if ($current->status !== 'pending') {
                throw new ApprovalException('El paso actual ya fue gestionado.', 409, 'APPROVAL_STEP_NOT_PENDING');
            }

            $now = now();

            $current->update([
                'status'              => 'approved',
                'acted_by'            => $user->id,
                'acted_at'            => $now,
                'comment'             => $comment,

                'fechamodificacion'   => $now,
                'usuariomodificacion' => $user->id,
                'ipmodificacion'      => $ip,
            ]);

            $maxOrder = (int) $req->steps->max('step_order');

            if ((int)$req->current_step_order >= $maxOrder) {
                $req->update([
                    'status'              => 'approved',
                    'active_key'          => null,

                    'fechamodificacion'   => $now,
                    'usuariomodificacion' => $user->id,
                    'ipmodificacion'      => $ip,
                ]);
            } else {
                $req->update([
                    'current_step_order'  => (int)$req->current_step_order + 1,
                    'fechamodificacion'   => $now,
                    'usuariomodificacion' => $user->id,
                    'ipmodificacion'      => $ip,
                ]);
            }

            return $req->fresh()->load(['definition','steps']);
        });
    }

    public function reject(string $approvalRequestId, User $user, ?string $comment, string $ip): ApprovalRequest
    {
        return DB::transaction(function () use ($approvalRequestId, $user, $comment, $ip) {

            $req = $this->repo->lockRequest($approvalRequestId);
            if (!$req) {
                throw new ApprovalException('Solicitud no encontrada.', 404, 'APPROVAL_REQUEST_NOT_FOUND');
            }

            if ((int)$req->active_key !== 1 || $req->status !== 'pending') {
                throw new ApprovalException('La solicitud no está pendiente o no está activa.', 409, 'APPROVAL_NOT_ACTIONABLE');
            }

            $req->load(['steps','definition.steps']);

            $currentOrder = (int) $req->current_step_order;
            $current = $req->steps->firstWhere('step_order', $currentOrder);
            if (!$current) {
                throw new ApprovalException('Paso actual no encontrado.', 409, 'APPROVAL_CURRENT_STEP_MISSING');
            }

            $this->assertCanAct($user, $req, 'rechazar', $current->role_key);

            if ($current->status !== 'pending') {
                throw new ApprovalException('El paso actual ya fue gestionado.', 409, 'APPROVAL_STEP_NOT_PENDING');
            }

            $defStep  = $req->definition->steps->firstWhere('step_order', $currentOrder);
            $requires = (bool)($defStep?->requires_comment_on_reject ?? true);

            if ($requires && trim((string)$comment) === '') {
                throw new ApprovalException(
                    'Debes enviar un comentario para rechazar en este paso.',
                    422,
                    'APPROVAL_COMMENT_REQUIRED',
                    ['step_order' => $currentOrder, 'role_key' => $current->role_key]
                );
            }

            $now = now();

            $current->update([
                'status'              => 'rejected',
                'acted_by'            => $user->id,
                'acted_at'            => $now,
                'comment'             => $comment,

                'fechamodificacion'   => $now,
                'usuariomodificacion' => $user->id,
                'ipmodificacion'      => $ip,
            ]);

            foreach ($req->steps as $s) {
                if ((int)$s->step_order > $currentOrder && $s->status === 'pending') {
                    $s->update([
                        'status'              => 'skipped',
                        'fechamodificacion'   => $now,
                        'usuariomodificacion' => $user->id,
                        'ipmodificacion'      => $ip,
                    ]);
                }
            }

            $req->update([
                'status'              => 'rejected',
                'active_key'          => null,
                'fechamodificacion'   => $now,
                'usuariomodificacion' => $user->id,
                'ipmodificacion'      => $ip,
            ]);

            return $req->fresh()->load(['definition','steps']);
        });
    }

    public function cancel(string $approvalRequestId, User $user, string $ip): ApprovalRequest
    {
        return DB::transaction(function () use ($approvalRequestId, $user, $ip) {

            $req = $this->repo->lockRequest($approvalRequestId);
            if (!$req) throw new ApprovalException('Solicitud no encontrada.', 404, 'APPROVAL_REQUEST_NOT_FOUND');

            if ((int)$req->active_key !== 1 || $req->status !== 'pending') {
                throw new ApprovalException('La solicitud no está pendiente o no está activa.', 409, 'APPROVAL_NOT_ACTIONABLE');
            }

            $isAdmin = $user->hasRole('super_admin') || $user->hasRole('administrador') || $user->hasRole('admin');
            if (!$isAdmin && (int)$req->usuariocreacion !== (int)$user->id) {
                throw new ApprovalException('No tienes permisos para cancelar esta solicitud.', 403, 'APPROVAL_FORBIDDEN');
            }

            $req->load('steps');

            $now = now();

            foreach ($req->steps as $s) {
                if ($s->status === 'pending') {
                    $s->update([
                        'status'              => 'skipped',
                        'fechamodificacion'   => $now,
                        'usuariomodificacion' => $user->id,
                        'ipmodificacion'      => $ip,
                    ]);
                }
            }

            $req->update([
                'status'              => 'cancelled',
                'active_key'          => null,
                'fechamodificacion'   => $now,
                'usuariomodificacion' => $user->id,
                'ipmodificacion'      => $ip,
            ]);

            return $req->fresh()->load(['definition','steps']);
        });
    }

    private function assertCanAct(User $user, ApprovalRequest $req, string $action, string $roleKey): void
    {
        $base = $this->permBaseFromApprovable($req->approvable_type);

        $perm = "{$base}.{$action}.{$roleKey}"; // aprobar|rechazar

        if (
            $user->hasRole('super_admin') ||
            $user->hasRole('administrador') ||
            $user->hasRole('admin') ||
            $user->can($perm)
        ) {
            return;
        }

        throw new ApprovalException(
            message: 'No tienes permisos para actuar en este paso.',
            statusCode: 403,
            errorCode: 'APPROVAL_FORBIDDEN',
            details: ['required_permission' => $perm, 'role_key' => $roleKey]
        );
    }

    private function permBaseFromApprovable(string $approvableType): string
    {
        return match ($approvableType) {
            \App\Models\Creacion::class     => 'creaciones',
            \App\Models\Programacion::class => 'programaciones',
            default                        => 'approvals',
        };
    }
}
