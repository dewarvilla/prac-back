<?php

namespace App\Services;

use App\Exceptions\Approvals\ApprovalAlreadyActiveException;
use App\Exceptions\Approvals\ApprovalAlreadyFinalizedException;
use App\Exceptions\Approvals\ApprovalCancelNotAllowedException;
use App\Exceptions\Approvals\ApprovalCurrentStepMissingException;
use App\Exceptions\Approvals\ApprovalDefinitionInactiveException;
use App\Exceptions\Approvals\ApprovalDefinitionNoStepsException;
use App\Exceptions\Approvals\ApprovalDefinitionNotFoundException;
use App\Exceptions\Approvals\ApprovalNotActionableException;
use App\Exceptions\Approvals\ApprovalRequestIdNotFoundException;
use App\Exceptions\Approvals\ApprovalStepNotPendingException;
use App\Exceptions\Approvals\ApproverNotAllowedException;
use App\Exceptions\Approvals\MissingRejectionCommentException;

use App\Models\ApprovalRequest;
use App\Models\ApprovalStep;
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
        return DB::transaction(function () use ($approvable, $definitionCode, $user) {

            $def = $this->repo->findDefinitionByCode($definitionCode);

            if (!$def) {
                throw new ApprovalDefinitionNotFoundException($definitionCode);
            }

            if (!$def->is_active) {
                throw new ApprovalDefinitionInactiveException($definitionCode);
            }

            if ($def->steps->isEmpty()) {
                throw new ApprovalDefinitionNoStepsException($definitionCode);
            }

            $active = $this->repo->findActiveRequestFor($approvable);
            if ($active) {
                throw new ApprovalAlreadyActiveException(
                    $approvable::class,
                    (string) $approvable->getKey(),
                    (string) $active->id
                );
            }

            $firstStep  = $def->steps->sortBy('step_order')->first();
            $firstOrder = (int) $firstStep->step_order;

            $req = $this->repo->createRequest([
                'approvable_type'        => $approvable::class,
                'approvable_id'          => (string) $approvable->getKey(),
                'approval_definition_id' => (string) $def->id,

                'status'             => 'pending',
                'current_step_order' => $firstOrder,

                // Auditoría central
                'is_current'   => true,
                'requested_by' => $user?->id,
                'closed_at'    => null,

            ]);

            $rows = [];
            foreach ($def->steps->sortBy('step_order') as $s) {
                $rows[] = [
                    'id'                  => (string) Str::uuid(),
                    'approval_request_id' => (string) $req->id,
                    'step_order'          => (int) $s->step_order,
                    'role_key'            => (string) $s->role_key,
                    'status'              => 'pending',
                    'acted_by'            => null,
                    'acted_at'            => null,
                    'comment'             => null,
                ];
            }

            $this->repo->createStepsBulk($rows);

            DB::afterCommit(function () use ($req) {
                app(\App\Services\ApprovalNotificationService::class)
                    ->notifyCurrentApprovers((string) $req->id);
            });

            return $req->fresh()->load(['definition', 'steps']);
        });
    }

    public function approve(string $approvalRequestId, User $user, ?string $comment, string $ip): ApprovalRequest
    {
        return DB::transaction(function () use ($approvalRequestId, $user, $comment, $ip) {

            $req = $this->repo->lockRequest($approvalRequestId);
            if (!$req) {
                throw new ApprovalRequestIdNotFoundException($approvalRequestId);
            }

            $this->assertActionable($req);

            $req->load(['steps', 'definition.steps']);

            $current = $req->steps->firstWhere('step_order', (int) $req->current_step_order);
            if (!$current) {
                throw new ApprovalCurrentStepMissingException((string) $req->id, (int) $req->current_step_order);
            }

            $this->assertCanAct($user, $req, 'aprobar', (string) $current->role_key);

            if ($current->status !== 'pending') {
                throw new ApprovalStepNotPendingException((string) $current->status);
            }

            $now = now();

            $current->update([
                'status'   => 'approved',
                'acted_by' => $user->id,
                'acted_at' => $now,
                'comment'  => $comment,
            ]);

            $maxOrder = (int) $req->steps->max('step_order');
            $isFinal  = ((int) $req->current_step_order >= $maxOrder);

            if ($isFinal) {
                $req->update([
                    'status'     => 'approved',
                    'is_current' => false,
                    'closed_at'  => $now,
                ]);

                $this->applyFinalStatusToApprovable($req, 'approved', $user, $ip);

            } else {
                $nextOrder = (int) $req->current_step_order + 1;
                $nextStep  = $req->steps->firstWhere('step_order', $nextOrder);

                if (!$nextStep) {
                    throw new ApprovalCurrentStepMissingException((string) $req->id, $nextOrder);
                }

                $req->update([
                    'current_step_order' => $nextOrder,
                ]);
            }

            DB::afterCommit(function () use ($req, $isFinal, $comment) {
                $notifier = app(\App\Services\ApprovalNotificationService::class);

                if ($isFinal) {
                    $notifier->notifyCreatorStatus((string) $req->id, 'approved', $comment);
                } else {
                    $notifier->notifyCurrentApprovers((string) $req->id);
                    $notifier->notifyCreatorStatus((string) $req->id, 'pending', $comment);
                }
            });

            return $req->fresh()->load(['definition', 'steps']);
        });
    }

    public function reject(string $approvalRequestId, User $user, ?string $comment, string $ip): ApprovalRequest
    {
        return DB::transaction(function () use ($approvalRequestId, $user, $comment, $ip) {

            $req = $this->repo->lockRequest($approvalRequestId);
            if (!$req) {
                throw new ApprovalRequestIdNotFoundException($approvalRequestId);
            }

            $this->assertActionable($req);

            $req->load(['steps', 'definition.steps']);

            $currentOrder = (int) $req->current_step_order;
            $current      = $req->steps->firstWhere('step_order', $currentOrder);

            if (!$current) {
                throw new ApprovalCurrentStepMissingException((string) $req->id, $currentOrder);
            }

            $this->assertCanAct($user, $req, 'rechazar', (string) $current->role_key);

            if ($current->status !== 'pending') {
                throw new ApprovalStepNotPendingException((string) $current->status);
            }

            $defStep  = $req->definition->steps->firstWhere('step_order', $currentOrder);
            $requires = (bool) ($defStep?->requires_comment_on_reject ?? true);

            if ($requires && trim((string) $comment) === '') {
                throw new MissingRejectionCommentException($currentOrder, (string) $current->role_key);
            }

            $now = now();

            $current->update([
                'status'   => 'rejected',
                'acted_by' => $user->id,
                'acted_at' => $now,
                'comment'  => $comment,
            ]);

            ApprovalStep::query()
                ->where('approval_request_id', (string) $req->id)
                ->where('step_order', '>', $currentOrder)
                ->where('status', 'pending')
                ->update([
                    'status'     => 'skipped',
                    'updated_at' => $now,
                ]);

            $req->update([
                'status'     => 'rejected',
                'is_current' => false,
                'closed_at'  => $now,
            ]);

            $this->applyFinalStatusToApprovable($req, 'rejected', $user, $ip);

            DB::afterCommit(function () use ($req, $comment) {
                app(\App\Services\ApprovalNotificationService::class)
                    ->notifyCreatorStatus((string) $req->id, 'rejected', $comment);
            });

            return $req->fresh()->load(['definition', 'steps']);
        });
    }

    public function cancel(string $approvalRequestId, User $user, string $ip): ApprovalRequest
    {
        return DB::transaction(function () use ($approvalRequestId, $user, $ip) {

            $req = $this->repo->lockRequest($approvalRequestId);
            if (!$req) {
                throw new ApprovalRequestIdNotFoundException($approvalRequestId);
            }

            $this->assertActionable($req);

            $isAdmin   = $user->hasRole('super_admin') || $user->hasRole('administrador') || $user->hasRole('admin');
            $creatorId = $req->requested_by ? (int) $req->requested_by : null;

            if (!$isAdmin && $creatorId !== (int) $user->id) {
                throw new ApprovalCancelNotAllowedException((string) $req->id, (int) $user->id, $creatorId);
            }

            $now = now();

            ApprovalStep::query()
                ->where('approval_request_id', (string) $req->id)
                ->where('status', 'pending')
                ->update([
                    'status'     => 'skipped',
                    'updated_at' => $now,
                ]);

            $req->update([
                'status'     => 'cancelled',
                'is_current' => false,
                'closed_at'  => $now,
            ]);

            $this->applyFinalStatusToApprovable($req, 'cancelled', $user, $ip);

            DB::afterCommit(function () use ($req) {
                app(\App\Services\ApprovalNotificationService::class)
                    ->notifyCreatorStatus((string) $req->id, 'cancelled', null);
            });

            return $req->fresh()->load(['definition', 'steps']);
        });
    }

    private function assertActionable(ApprovalRequest $req): void
    {
        if ($req->status !== 'pending') {
            throw new ApprovalAlreadyFinalizedException((string) $req->status);
        }

        if (!$req->is_current) {
            throw new ApprovalNotActionableException((string) $req->id, (string) $req->status, 0);
        }
    }

    private function assertCanAct(User $user, ApprovalRequest $req, string $action, string $roleKey): void
    {
        $perm = "approvals.{$action}.{$roleKey}";

        if (
            $user->hasRole('super_admin') ||
            $user->hasRole('administrador') ||
            $user->hasRole('admin') ||
            $user->can($perm)
        ) {
            return;
        }

        throw new ApproverNotAllowedException($roleKey, $perm);
    }

    private function applyFinalStatusToApprovable(ApprovalRequest $req, string $finalStatus, User $actor, string $ip): void
    {
        $now = now();

        if ($req->approvable_type === \App\Models\Creacion::class) {
            $estado = match ($finalStatus) {
                'approved'  => 'aprobada',
                'rejected'  => 'rechazada',
                'cancelled' => 'creada',
                default     => 'en_aprobacion',
            };

            \App\Models\Creacion::query()
                ->where('id', (string) $req->approvable_id)
                ->update([
                    'estado_creacion' => $estado,
                    'updated_at'      => $now,
                ]);
        }

        //Programacions aquí.
    }
}