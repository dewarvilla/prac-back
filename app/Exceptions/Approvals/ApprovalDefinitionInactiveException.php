<?php

namespace App\Exceptions\Approvals;

use App\Exceptions\ConflictException;

final class ApprovalDefinitionInactiveException extends ConflictException
{
    public function __construct(string $code)
    {
        parent::__construct(
            'El flujo de aprobación está inactivo.',
            'APPROVAL_DEFINITION_INACTIVE',
            ['definition_code' => $code]
        );
    }
}
