<?php

namespace App\Exceptions\Creaciones;

use App\Exceptions\ConflictException;

class CreacionDuplicateInCatalogoException extends ConflictException
{
    public function __construct(string $catalogoId, string $nombrePractica, ?string $ignoreId = null)
    {
        parent::__construct(
            message: 'Ya existe una práctica con ese nombre en el programa académico indicado.',
            errorCode: 'CREACION_DUPLICATE_IN_CATALOGO',
            details: array_filter([
                'catalogo_id'      => $catalogoId,
                'nombre_practica'  => $nombrePractica,
                'ignore_id'        => $ignoreId,
            ], fn ($v) => $v !== null)
        );
    }
}
