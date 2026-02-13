<?php

namespace App\Exceptions\Approvals;

use App\Exceptions\ConflictException;

class ApprovalAlreadyActiveException extends ConflictException
{
    public function __construct(string $approvableType, string $approvableId)
    {
        parent::__construct(
            'Ya existe una aprobación activa para este recurso.',
            'APPROVAL_ALREADY_ACTIVE',
            ['approvable_type' => $approvableType, 'approvable_id' => $approvableId]
        );
    }
}
