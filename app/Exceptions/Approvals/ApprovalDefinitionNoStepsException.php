<?php

namespace App\Exceptions\Approvals;

use App\Exceptions\ConflictException;

final class ApprovalDefinitionNoStepsException extends ConflictException
{
    public function __construct(string $code)
    {
        parent::__construct(
            'El flujo de aprobación no tiene pasos configurados.',
            'APPROVAL_DEFINITION_NO_STEPS',
            ['definition_code' => $code]
        );
    }
}
