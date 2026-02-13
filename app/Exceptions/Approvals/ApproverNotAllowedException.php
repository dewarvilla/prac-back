<?php

namespace App\Exceptions\Approvals;

use App\Exceptions\ForbiddenException;

class ApproverNotAllowedException extends ForbiddenException
{
    public function __construct(string $requiredRoleKey)
    {
        parent::__construct(
            'No tienes permisos para decidir esta aprobación.',
            'APPROVAL_APPROVER_NOT_ALLOWED',
            ['required_role_key' => $requiredRoleKey]
        );
    }
}
