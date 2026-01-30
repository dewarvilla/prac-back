<?php

namespace App\Http\Requests\V1\Catalogo\Concerns;

use Illuminate\Support\Str;

trait NormalizesCatalogoInput
{
    protected function normText($v): ?string
    {
        if ($v === null) return null;
        $v = preg_replace('/\s+/u', ' ', trim((string) $v));
        return $v === '' ? null : $v;
    }

    protected function normNivel($v): ?string
    {
        $v = $this->normText($v);
        return $v ? Str::lower($v) : null;
    }
    
    protected function normKey(?string $facultad, ?string $programa): string
    {
        $fac = $this->normText($facultad) ?? '';
        $pro = $this->normText($programa) ?? '';
        $fac = mb_strtolower($fac, 'UTF-8');
        $pro = mb_strtolower($pro, 'UTF-8');
        return "{$fac}|{$pro}";
    }
}
