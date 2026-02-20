<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'string'   => 'El campo :attribute debe ser texto.',
    'integer'  => 'El campo :attribute debe ser un número entero.',
    'numeric'  => 'El campo :attribute debe ser un número.',
    'boolean'  => 'El campo :attribute debe ser verdadero o falso.',
    'date'     => 'El campo :attribute debe ser una fecha válida.',
    'max' => [
        'string'  => 'El campo :attribute no debe ser mayor a :max caracteres.',
        'numeric' => 'El campo :attribute no debe ser mayor a :max.',
        'array'   => 'El campo :attribute no debe tener más de :max elementos.',
    ],
    'min' => [
        'string'  => 'El campo :attribute debe tener al menos :min caracteres.',
        'numeric' => 'El campo :attribute debe ser al menos :min.', 
        'array'   => 'El campo :attribute debe tener al menos :min elementos.',
    ],
    'between' => [
        'string'  => 'El campo :attribute debe tener entre :min y :max caracteres.',
        'numeric' => 'El campo :attribute debe estar entre :min y :max.',
        'array'   => 'El campo :attribute debe tener entre :min y :max elementos.',
    ],
    'in'      => 'El valor seleccionado para :attribute no es válido.',
    'exists'  => 'El valor seleccionado para :attribute no es válido.',
    'unique'  => 'El campo :attribute ya está registrado.',
    'uuid'    => 'El campo :attribute debe ser un UUID válido.',
    'and' => 'y :count error más|y :count errores más',

    'attributes' => [
        //campos de forms:
        'nombre_practica'     => 'Nombre práctica',
        'catalogo_id'         => 'Programa académico',
        'recursos_necesarios' => 'Recursos necesarios',
        'justificacion'       => 'Justificación',
        // 'fecha_inicio' => 'fecha de inicio',
        // 'fecha_fin'    => 'fecha de finalización',
    ],
];
