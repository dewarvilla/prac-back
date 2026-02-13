<?php

namespace App\Exceptions\Programaciones;

use App\Exceptions\UnprocessableEntityException;

class ProgramacionNotEditableException extends UnprocessableEntityException
{
    public function __construct(string $estadoPractica)
    {
        parent::__construct(
            'La programación no se puede modificar en el estado actual.',
            'PROGRAMACION_NOT_EDITABLE',
            ['estado_practica' => $estadoPractica]
        );
    }
}
