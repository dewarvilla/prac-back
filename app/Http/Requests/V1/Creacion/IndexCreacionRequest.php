<?php

namespace App\Http\Requests\V1\Creacion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\Concerns\TrimsStrings;
use App\Http\Requests\Concerns\NormalizesSort;

class IndexCreacionRequest extends FormRequest
{
    use TrimsStrings, NormalizesSort;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        foreach ([
            'q',
            'nombre_practica',
            'recursos_necesarios',
            'justificacion',
            'nombre_practica.lk',
        ] as $k) {
            if ($this->has($k)) $this->merge([$k => $this->normText($this->input($k))]);
        }

        $this->normalizeSortInput('sort');

        if ($this->has('catalogo_id')) {
            $this->merge(['catalogo_id' => (string) $this->input('catalogo_id')]);
        }
    }

    public function rules(): array
    {
        $sortable = [
            'id', '-id',
            'nombre_practica','-nombre_practica',
            'estado_creacion','-estado_creacion',
            'catalogo_id','-catalogo_id',
            'fechacreacion','-fechacreacion',
            'fechamodificacion','-fechamodificacion',
        ];

        return [
            'q'        => ['sometimes','nullable','string','max:255'],
            'per_page' => ['sometimes','integer','min:1','max:200'],
            'page'     => ['sometimes','integer','min:1'],

            'sort' => ['sometimes', function ($attr, $value, $fail) use ($sortable) {
                foreach (explode(',', (string) $value) as $p) {
                    $p = trim($p);
                    if ($p === '') continue;
                    if (!in_array($p, $sortable, true)) {
                        return $fail("El valor de sort '{$p}' no es permitido.");
                    }
                }
            }],

            'catalogo_id' => ['sometimes','uuid'],

            'nombre_practica'     => ['sometimes','string','max:255'],
            'recursos_necesarios' => ['sometimes','string'],
            'justificacion'       => ['sometimes','string'],

            'estado_creacion' => ['sometimes', Rule::in([
                'borrador','en_aprobacion','aprobada','rechazada','creada'
            ])],

            'nombre_practica.lk' => ['sometimes','string','max:255'],
        ];
    }
}
