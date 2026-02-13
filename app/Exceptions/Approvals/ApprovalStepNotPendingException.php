<?php

namespace App\Exceptions\Approvals;

use App\Exceptions\ConflictException;

class ApprovalStepNotPendingException extends ConflictException
{
    public function __construct(string $status)
    {
        parent::__construct(
            'El paso actual ya no está pendiente.',
            'APPROVAL_STEP_NOT_PENDING',
            ['step_status' => $status]
        );
    }
}
