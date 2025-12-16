<?php

namespace App\Http\Requests\V1\Catalogo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class IndexCatalogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('sort')) {
            $raw = (string) $this->input('sort');

            $parts = array_map('trim', explode(',', $raw));

            $norm = array_map(function ($p) {
                $dir = 1;
                if (Str::startsWith($p, '-')) {
                    $dir = -1;
                    $p = ltrim($p, '-');
                }

                $p = Str::snake($p);

                return $dir === -1 ? "-{$p}" : $p;
            }, $parts);

            $this->merge(['sort' => implode(',', $norm)]);
        }
    }

    public function rules(): array
    {
        $sortable = [
            'id','-id',
            'facultad','-facultad',
            'nivel_academico','-nivel_academico',
            'programa_academico','-programa_academico',
        ];

        return [
            'q'        => ['sometimes','string','max:255'],
            'per_page' => ['sometimes','integer','min:1','max:200'],
            'page'     => ['sometimes','integer','min:1'],

            'sort' => ['sometimes', function($attr,$value,$fail) use ($sortable) {
                foreach (explode(',', (string)$value) as $part) {
                    if (!in_array(trim($part), $sortable, true)) {
                        return $fail("El valor de sort '{$part}' no es permitido.");
                    }
                }
            }],

            'nivel_academico'    => ['sometimes', Rule::in(['pregrado','postgrado'])],
            'facultad'           => ['sometimes','string','max:255'],
            'programa_academico' => ['sometimes','string','max:255'],

            'facultad.lk'           => ['sometimes','string','max:255'],
            'programa_academico.lk' => ['sometimes','string','max:255'],
            'nivel_academico.lk'    => ['sometimes','string','max:255'],
        ];
    }
}
