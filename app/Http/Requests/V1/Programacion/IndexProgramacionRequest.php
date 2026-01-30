<?php

namespace App\Http\Requests\V1\Programacion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\V1\Programacion\Concerns\NormalizesProgramacionInput;

class IndexProgramacionRequest extends FormRequest
{
    use NormalizesProgramacionInput;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        // normaliza textos
        foreach ([
            'q',
            'nombre_practica',
            'lugar_de_realizacion',
        ] as $k) {
            if ($this->has($k)) $this->merge([$k => $this->normText($this->input($k))]);
        }

        // sort normalizado
        if ($this->has('sort')) {
            $this->merge(['sort' => $this->normSort($this->input('sort'))]);
        }

        // casts básicos
        if ($this->has('creacion_id'))        $this->merge(['creacion_id' => (int) $this->input('creacion_id')]);
        if ($this->has('requiere_transporte')) $this->merge(['requiere_transporte' => filter_var($this->input('requiere_transporte'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)]);
        if ($this->has('per_page'))           $this->merge(['per_page' => (int) $this->input('per_page')]);
        if ($this->has('page'))               $this->merge(['page' => (int) $this->input('page')]);
    }

    public function rules(): array
    {
        $sortable = [
            'id','-id',
            'fecha_inicio','-fecha_inicio',
            'fecha_finalizacion','-fecha_finalizacion',
            'estado_practica','-estado_practica',
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

            // filtros
            'creacion_id' => ['sometimes','integer','min:1'],
            'nombre_practica' => ['sometimes','string','max:255'],
            'lugar_de_realizacion' => ['sometimes','nullable','string','max:255'],

            'fecha_inicio' => ['sometimes','date'],
            'fecha_finalizacion' => ['sometimes','date','after_or_equal:fecha_inicio'],

            'requiere_transporte' => ['sometimes','boolean'],

            'estado_practica' => ['sometimes', Rule::in([
                'en_aprobacion',
                'aprobada',
                'rechazada',
                'en_ejecucion',
                'ejecutada',
                'en_legalizacion',
                'legalizada'
            ])],
        ];
    }
}
