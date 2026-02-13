<?php

namespace App\Exceptions\Programaciones;

use App\Exceptions\ConflictException;

class ProgramacionDuplicateNombreFechasException extends ConflictException
{
    public function __construct(string $nombrePractica, string $fechaInicio, string $fechaFinalizacion, ?string $ignoreId = null)
    {
        parent::__construct(
            message: 'Ya existe una programación con el mismo nombre de práctica y rango de fechas.',
            errorCode: 'PROGRAMACION_DUPLICATE_NOMBRE_FECHAS',
            details: array_filter([
                'nombre_practica'    => $nombrePractica,
                'fecha_inicio'       => $fechaInicio,
                'fecha_finalizacion' => $fechaFinalizacion,
                'ignore_id'          => $ignoreId,
            ], fn($v) => $v !== null)
        );
    }
}
