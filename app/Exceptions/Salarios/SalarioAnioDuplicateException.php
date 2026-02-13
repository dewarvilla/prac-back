<?php

namespace App\Exceptions\Salarios;

use App\Exceptions\ConflictException;

class SalarioAnioDuplicateException extends ConflictException
{
    public function __construct(int $anio, ?string $ignoreId = null)
    {
        parent::__construct(
            message: 'Ya existe un salario registrado para ese año.',
            errorCode: 'SALARIO_ANIO_DUPLICATE',
            details: array_filter([
                'anio'       => $anio,
                'ignore_id'  => $ignoreId,
            ], fn($v) => $v !== null)
        );
    }
}
