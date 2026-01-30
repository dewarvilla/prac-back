<?php

namespace App\Http\Requests\V1\Creacion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\V1\Creacion\Concerns\NormalizesCreacionInput;

class IndexCreacionRequest extends FormRequest
{
    use NormalizesCreacionInput;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        // normaliza texto
        foreach ([
            'q',
            'nombre_practica',
            'recursos_necesarios',
            'justificacion',
            'nombre_practica.lk',
        ] as $k) {
            if ($this->has($k)) $this->merge([$k => $this->normText($this->input($k))]);
        }

        // sort normalizado
        if ($this->has('sort')) {
            $this->merge(['sort' => $this->normSort($this->input('sort'))]);
        }

        // catálogo id
        if ($this->has('catalogo_id')) {
            $this->merge(['catalogo_id' => (int) $this->input('catalogo_id')]);
        }
    }

    public function rules(): array
    {
        $sortable = [
            'id','-id',
            'nombre_practica','-nombre_practica',
            'estado_creacion','-estado_creacion',
            'catalogo_id','-catalogo_id',
            'fechacreacion','-fechacreacion',
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

            // filtros directos
            'catalogo_id' => ['sometimes','integer','min:1'],

            'nombre_practica'     => ['sometimes','string','max:255'],
            'recursos_necesarios' => ['sometimes','string'],
            'justificacion'       => ['sometimes','string'],

            'estado_creacion' => ['sometimes', Rule::in(['creada','en_aprobacion','aprobada','rechazada'])],

            'estado_comite_acreditacion' => ['sometimes', Rule::in(['aprobada','rechazada','pendiente'])],
            'estado_consejo_facultad'    => ['sometimes', Rule::in(['aprobada','rechazada','pendiente'])],
            'estado_consejo_academico'   => ['sometimes', Rule::in(['aprobada','rechazada','pendiente'])],

            // filtros tipo "like"
            'nombre_practica.lk' => ['sometimes','string','max:255'],
        ];
    }
}
