<?php

namespace App\Http\Requests\V1\Reprogramacion\Concerns;

use Illuminate\Support\Str;

trait NormalizesReprogramacionInput
{
    protected function normText($v): ?string
    {
        if ($v === null) return null;
        $v = preg_replace('/\s+/u', ' ', trim((string) $v));
        return $v === '' ? null : $v;
    }

    protected function normSort($raw): ?string
    {
        if ($raw === null) return null;
        $raw = (string) $raw;
        $parts = array_filter(array_map('trim', explode(',', $raw)));
        if (!$parts) return null;

        $norm = array_map(function ($p) {
            $desc = Str::startsWith($p, '-');
            $p = Str::snake(ltrim($p, '-'));
            return $desc ? "-{$p}" : $p;
        }, $parts);

        return implode(',', $norm);
    }
}
