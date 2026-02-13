<?php

namespace App\Exceptions\Programaciones;

use App\Exceptions\ConflictException;

class ProgramacionNivelFormacionNoResueltoException extends ConflictException
{
    public function __construct(?string $nivelAcademico)
    {
        parent::__construct(
            message: 'No se pudo determinar el nivel de formación de la programación desde el catálogo.',
            errorCode: 'PROGRAMACION_NIVEL_FORMACION_NO_RESUELTO',
            details: [
                'nivel_academico_catalogo' => $nivelAcademico,
            ]
        );
    }
}
