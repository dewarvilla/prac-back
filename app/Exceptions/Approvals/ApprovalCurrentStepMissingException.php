<?php

namespace App\Exceptions\Approvals;

use App\Exceptions\ConflictException;

final class ApprovalCurrentStepMissingException extends ConflictException
{
    public function __construct(string $approvalRequestId, int $currentStepOrder)
    {
        parent::__construct(
            'Paso actual no encontrado.',
            'APPROVAL_CURRENT_STEP_MISSING',
            [
                'approval_request_id' => $approvalRequestId,
                'current_step_order'  => $currentStepOrder,
            ]
        );
    }
}
