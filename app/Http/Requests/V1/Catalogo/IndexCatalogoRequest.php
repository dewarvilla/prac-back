<?php

namespace App\Http\Requests\V1\Catalogo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\Concerns\TrimsStrings;
use App\Http\Requests\Concerns\NormalizesSort;

class IndexCatalogoRequest extends FormRequest
{
    use TrimsStrings, NormalizesSort;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        foreach ([
            'q',
            'facultad',
            'programa_academico',
            'facultad.lk',
            'programa_academico.lk',
        ] as $k) {
            if ($this->has($k)) {
                $this->merge([$k => $this->normText($this->input($k))]);
            }
        }

        if ($this->has('nivel_academico')) {
            $this->merge(['nivel_academico' => $this->normText($this->input('nivel_academico'))]);
        }

        $this->normalizeSortInput('sort');

        if ($this->has('estado')) {
            $this->merge([
                'estado' => filter_var($this->input('estado'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }

    public function rules(): array
    {
        $sortable = [
            'id','-id',
            'nivel_academico','-nivel_academico',
            'facultad','-facultad',
            'programa_academico','-programa_academico',
            'estado','-estado',
            'created_at','-created_at',
            'updated_at','-updated_at',
        ];

        return [
            'q'        => ['sometimes','nullable','string','max:255'],
            'per_page' => ['sometimes','integer','min:1','max:200'],
            'page'     => ['sometimes','integer','min:1'],

            'sort' => ['sometimes', function ($attr, $value, $fail) use ($sortable) {
                $p = trim((string) $value);
                if ($p === '') return;
                if (!in_array($p, $sortable, true)) {
                    return $fail("El valor de sort '{$p}' no es permitido.");
                }
            }],

            'nivel_academico' => ['sometimes', Rule::in(['pregrado','postgrado'])],
            'facultad'        => ['sometimes','string','max:255'],
            'programa_academico' => ['sometimes','string','max:255'],

            'estado' => ['sometimes','nullable','boolean'],

            'facultad.lk'           => ['sometimes','string','max:255'],
            'programa_academico.lk' => ['sometimes','string','max:255'],
        ];
    }
}