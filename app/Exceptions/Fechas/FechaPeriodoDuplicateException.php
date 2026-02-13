<?php

namespace App\Exceptions\Fechas;

use App\Exceptions\ConflictException;

class FechaPeriodoDuplicateException extends ConflictException
{
    public function __construct(string $periodo, ?string $ignoreId = null)
    {
        parent::__construct(
            message: 'Ya existe un registro de fechas para ese periodo.',
            errorCode: 'FECHA_PERIODO_DUPLICATE',
            details: array_filter([
                'periodo'    => $periodo,
                'ignore_id'  => $ignoreId,
            ], fn($v) => $v !== null)
        );
    }
}
