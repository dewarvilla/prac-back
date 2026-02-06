<?php

namespace App\Repositories\Ruta;

use App\Models\Ruta;

class RutapeajeRepository
{
    private const MAP = [
        'I'   => 'cat_i',
        'II'  => 'cat_ii',
        'III' => 'cat_iii',
        'IV'  => 'cat_iv',
        'V'   => 'cat_v',
        'VI'  => 'cat_vi',
        'VII' => 'cat_vii',
    ];

    public function totalsByCategoria(Ruta $ruta): array
    {
        $totales = [];
        foreach (self::MAP as $cat => $col) {
            $totales[$cat] = (float) $ruta->peajes()->sum($col);
        }
        return $totales;
    }

    public function totalForCategoria(Ruta $ruta, string $cat): float
    {
        $col = $this->columnForCategoria($cat);
        return (float) $ruta->peajes()->sum($col);
    }

    public function columnForCategoria(string $cat): string
    {
        $cat = strtoupper(trim($cat));

        if (!isset(self::MAP[$cat])) {
            throw new \InvalidArgumentException(
                'Categoría inválida. Use: '.implode(',', array_keys(self::MAP))
            );
        }

        return self::MAP[$cat];
    }

    public function categoriasValidas(): array
    {
        return array_keys(self::MAP);
    }
}
