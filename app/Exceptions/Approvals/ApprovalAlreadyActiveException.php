<?php

namespace App\Exceptions\Approvals;

use App\Exceptions\ConflictException;

final class ApprovalAlreadyActiveException extends ConflictException
{
    public function __construct(string $approvableType, string $approvableId, ?string $approvalRequestId = null)
    {
        $details = [
            'approvable_type' => $approvableType,
            'approvable_id'   => $approvableId,
        ];

        if ($approvalRequestId) {
            $details['approval_request_id'] = $approvalRequestId;
        }

        parent::__construct(
            'Ya existe una aprobación activa para este recurso.',
            'APPROVAL_ALREADY_ACTIVE',
            $details
        );
    }
}
