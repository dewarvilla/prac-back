<?php

namespace App\Repositories\Approval;

use App\Models\ApprovalDefinition;
use App\Models\ApprovalRequest;
use Illuminate\Database\Eloquent\Model;

interface ApprovalInterface
{
    public function findDefinitionByCode(string $code): ?ApprovalDefinition;

    public function findActiveRequestFor(Model $approvable): ?ApprovalRequest;

    public function createRequest(array $data): ApprovalRequest;

    public function createStepsBulk(array $rows): void;

    public function lockRequest(string $id): ?ApprovalRequest;
}
