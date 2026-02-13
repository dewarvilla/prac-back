<?php

namespace App\Exceptions\Approvals;

use App\Exceptions\NotFoundException;

class ApprovalRequestNotFoundException extends NotFoundException
{
    public function __construct(string $approvableType, string $approvableId)
    {
        parent::__construct(
            'No existe una solicitud de aprobación para este recurso.',
            'APPROVAL_REQUEST_NOT_FOUND',
            ['approvable_type' => $approvableType, 'approvable_id' => $approvableId]
        );
    }
}
