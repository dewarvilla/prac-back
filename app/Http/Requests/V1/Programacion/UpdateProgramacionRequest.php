<?php

namespace App\Http\Requests\V1\Programacion;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\V1\Programacion\Concerns\NormalizesProgramacionInput;

class UpdateProgramacionRequest extends FormRequest
{
    use NormalizesProgramacionInput;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $merged = [];

        if ($this->has('creacion_id')) $merged['creacion_id'] = (int) $this->input('creacion_id');

        foreach (['descripcion','lugar_de_realizacion','justificacion','recursos_necesarios'] as $k) {
            if ($this->has($k)) {
                $merged[$k] = $k === 'lugar_de_realizacion'
                    ? $this->normText($this->input($k))
                    : $this->normText($this->input($k));
            }
        }

        if ($this->has('requiere_transporte')) {
            $merged['requiere_transporte'] = filter_var($this->input('requiere_transporte'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        if ($this->has('numero_estudiantes')) $merged['numero_estudiantes'] = (int) $this->input('numero_estudiantes');

        if ($merged) $this->merge($merged);
    }

    public function rules(): array
    {
        $base = [
            'creacion_id'          => ['integer','min:1','exists:creaciones,id'],

            // lo decide el service desde creacion
            'nombre_practica'      => ['prohibited'],

            'descripcion'          => ['string'],
            'lugar_de_realizacion' => ['nullable','string','max:255'],
            'justificacion'        => ['string'],
            'recursos_necesarios'  => ['string'],
            'requiere_transporte'  => ['boolean'],
            'numero_estudiantes'   => ['integer','between:1,100'],

            'fecha_inicio'         => ['date'],
            'fecha_finalizacion'   => ['date','after_or_equal:fecha_inicio'],

            // estados por flujo interno
            'estado_practica'     => ['prohibited'],
            'estado_depart'       => ['prohibited'],
            'estado_postg'        => ['prohibited'],
            'estado_decano'       => ['prohibited'],
            'estado_jefe_postg'   => ['prohibited'],
            'estado_vice'         => ['prohibited'],
        ];

        if ($this->isMethod('patch')) {
            return collect($base)->map(fn($r) => array_merge(['sometimes'], $r))->all();
        }

        // PUT requiere cuerpo completo
        return array_merge($base, [
            'creacion_id'          => ['required','integer','min:1','exists:creaciones,id'],
            'descripcion'          => ['required','string'],
            'justificacion'        => ['required','string'],
            'recursos_necesarios'  => ['required','string'],
            'requiere_transporte'  => ['required','boolean'],
            'numero_estudiantes'   => ['required','integer','between:1,100'],
            'fecha_inicio'         => ['required','date'],
            'fecha_finalizacion'   => ['required','date','after_or_equal:fecha_inicio'],
        ]);
    }
}
