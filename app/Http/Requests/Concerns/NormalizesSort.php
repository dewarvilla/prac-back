<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Str;

trait NormalizesSort
{
    protected function normalizeSortInput(string $key = 'sort'): void
    {
        if (!$this->has($key)) return;

        $raw = (string) $this->input($key);
        $parts = array_filter(array_map('trim', explode(',', $raw)));

        $norm = array_map(function ($p) {
            $desc  = Str::startsWith($p, '-');
            $field = Str::snake(ltrim($p, '-'));
            return $desc ? "-{$field}" : $field;
        }, $parts);

        $this->merge([$key => implode(',', $norm)]);
    }
}
