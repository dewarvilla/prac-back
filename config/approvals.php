<?php

return [
    'notifications' => [
        'channels' => ['database'],
    ],

    'role_map' => [
        // Programaciones
        'departamento'     => ['jefe_departamento'],
        'postgrados'       => ['coordinador_postgrados'],
        'decano'           => ['decano'],
        'jefe_postgrados'  => ['jefe_postgrados'],
        'vicerrectoria'    => ['vicerrectoria'],

        // Creaciones
        'comite_acreditacion' => ['comite_acreditacion'],
        'consejo_facultad'    => ['consejo_facultad'],
        'consejo_academico'   => ['consejo_academico'],
    ],
];
