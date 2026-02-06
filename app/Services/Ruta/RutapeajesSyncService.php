<?php

namespace App\Services\Ruta;

use App\Models\Ruta;

class RutapeajesSyncService
{
    /**
     * Sincroniza peajes desde fuente externa (Socrata u otra).
     * Debe:
     * - leer la polyline de la ruta
     * - consultar la fuente
     * - insertar/actualizar Rutapeaje(s)
     * - actualizar total y numero_peajes en Ruta
     */
    public function syncFromSocrata(Ruta $ruta, string $categoria): array
    {
        // TODO: pega tu implementación real aquí.
        // Debe retornar al menos:
        // [
        //   'insertados' => int,
        //   'total_valor' => float|int,
        //   ...
        // ]

        // Ejemplo placeholder:
        return [
            'insertados' => 0,
            'total_valor' => 0,
        ];
    }
}
