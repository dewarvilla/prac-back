<?php

namespace App\Exceptions\Programaciones;

use App\Exceptions\ForbiddenException;

class ProgramacionWindowClosedException extends ForbiddenException
{
    public function __construct(string $mensaje = 'Esta acción solo está permitida dentro del periodo autorizado. Favor comunicarse con Vicerrectoría Académica.')
    {
        parent::__construct(
            $mensaje,
            'PROGRAMACION_WINDOW_CLOSED',
            []
        );
    }
}
