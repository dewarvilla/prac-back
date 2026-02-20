<?php

namespace App\Exceptions\Approvals;

use App\Exceptions\ForbiddenException;

final class ApproverNotAllowedException extends ForbiddenException
{
    public function __construct(string $roleKey, ?string $requiredPermission = null)
    {
        $details = ['role_key' => $roleKey];

        if ($requiredPermission) {
            $details['required_permission'] = $requiredPermission;
        }

        parent::__construct(
            'No tienes permisos para decidir esta aprobación.',
            'APPROVAL_APPROVER_NOT_ALLOWED',
            $details
        );
    }
}
