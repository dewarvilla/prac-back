<?php

namespace App\Http\Requests\V1\Catalogo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Http\Requests\V1\Catalogo\Concerns\NormalizesCatalogoInput;

class IndexCatalogoRequest extends FormRequest
{
    use NormalizesCatalogoInput;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if ($this->has('q')) {
            $this->merge(['q' => $this->normText($this->input('q'))]);
        }

        if ($this->has('facultad')) {
            $this->merge(['facultad' => $this->normText($this->input('facultad'))]);
        }

        if ($this->has('programa_academico')) {
            $this->merge(['programa_academico' => $this->normText($this->input('programa_academico'))]);
        }

        if ($this->has('nivel_academico')) {
            $this->merge(['nivel_academico' => $this->normNivel($this->input('nivel_academico'))]);
        }

        // sort: "campo,-campo" -> snake_case
        if ($this->has('sort')) {
            $raw = (string) $this->input('sort');
            $parts = array_filter(array_map('trim', explode(',', $raw)));

            $norm = array_map(function ($p) {
                $desc = Str::startsWith($p, '-');
                $p = ltrim($p, '-');
                $p = Str::snake($p);
                return $desc ? "-{$p}" : $p;
            }, $parts);

            $this->merge(['sort' => implode(',', $norm)]);
        }

        // lk (like) fields
        foreach (['facultad.lk','programa_academico.lk','nivel_academico.lk'] as $k) {
            if ($this->has($k)) {
                $this->merge([$k => $this->normText($this->input($k))]);
            }
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
            'q'        => ['sometimes','nullable','string','max:255'],
            'per_page' => ['sometimes','integer','min:1','max:200'],
            'page'     => ['sometimes','integer','min:1'],

            'sort' => ['sometimes', function ($attr, $value, $fail) use ($sortable) {
                foreach (explode(',', (string) $value) as $part) {
                    $part = trim($part);
                    if ($part === '') continue;
                    if (!in_array($part, $sortable, true)) {
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
