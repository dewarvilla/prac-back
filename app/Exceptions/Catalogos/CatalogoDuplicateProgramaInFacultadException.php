<?php

namespace App\Exceptions\Catalogos;

use App\Exceptions\ConflictException;

class CatalogoDuplicateProgramaInFacultadException extends ConflictException
{
    public function __construct(string $facultad, string $programaAcademico, ?string $ignoreId = null)
    {
        parent::__construct(
            message: 'Ya existe ese programa en la facultad indicada.',
            errorCode: 'CATALOGO_DUPLICATE_PROGRAMA_FACULTAD',
            details: array_filter([
                'facultad'           => $facultad,
                'programa_academico' => $programaAcademico,
                'ignore_id'          => $ignoreId,
            ], fn ($v) => $v !== null)
        );
    }
}
