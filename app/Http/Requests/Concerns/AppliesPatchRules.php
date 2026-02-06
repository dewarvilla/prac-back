<?php

namespace App\Http\Requests\Concerns;

trait AppliesPatchRules
{
    protected function patchify(array $rules): array
    {
        if (!$this->isMethod('patch')) return $rules;

        return collect($rules)->map(function ($r) {
            return is_array($r) ? array_merge(['sometimes'], $r) : $r;
        })->all();
    }
}
