<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Str;

trait NormalizesCommon
{
    use TrimsStrings;

    protected function normLower($v): ?string
    {
        $v = $this->normText($v);
        return $v ? Str::lower($v) : null;
    }

    /**
     * Key normalizada para detectar duplicados tipo (facultad|programa).
     */
    protected function normKey(?string $a, ?string $b): string
    {
        $x = $this->normText($a) ?? '';
        $y = $this->normText($b) ?? '';

        $x = mb_strtolower($x, 'UTF-8');
        $y = mb_strtolower($y, 'UTF-8');

        return "{$x}|{$y}";
    }
}
