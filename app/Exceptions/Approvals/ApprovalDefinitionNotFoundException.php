<?php

namespace App\Exceptions\Approvals;

use App\Exceptions\NotFoundException;

class ApprovalDefinitionNotFoundException extends NotFoundException
{
    public function __construct(string $code)
    {
        parent::__construct(
            'No existe una definición de aprobación para este tipo de proceso.',
            'APPROVAL_DEFINITION_NOT_FOUND',
            ['definition_code' => $code]
        );
    }
}
