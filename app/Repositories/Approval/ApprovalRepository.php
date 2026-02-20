<?php

namespace App\Repositories\Approval;

use App\Models\ApprovalDefinition;
use App\Models\ApprovalRequest;
use App\Models\ApprovalStep;
use Illuminate\Database\Eloquent\Model;

class ApprovalRepository implements ApprovalInterface
{
    public function findDefinitionByCode(string $code): ?ApprovalDefinition
    {
        return ApprovalDefinition::query()
            ->where('code', $code)
            ->with(['steps' => fn ($q) => $q->orderBy('step_order')])
            ->first();
    }

    public function findActiveRequestFor(Model $approvable): ?ApprovalRequest
    {
        return ApprovalRequest::query()
            ->where('approvable_type', $approvable::class)
            ->where('approvable_id', (string) $approvable->getKey())
            ->where('status', 'pending')
            ->where('is_current', true)
            ->first();
    }

    public function createRequest(array $data): ApprovalRequest
    {
        return ApprovalRequest::query()->create($data);
    }

    public function createStepsBulk(array $rows): void
    {
        ApprovalStep::query()->insert($rows);
    }

    public function lockRequest(string $id): ?ApprovalRequest
    {
        return ApprovalRequest::query()
            ->where('id', $id)
            ->lockForUpdate()
            ->first();
    }
}