<?php

namespace App\Http\Requests\V1\Programacion;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\V1\Programacion\Concerns\NormalizesProgramacionInput;

class StoreProgramacionRequest extends FormRequest
{
    use NormalizesProgramacionInput;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'creacion_id'          => $this->has('creacion_id') ? (int) $this->input('creacion_id') : null,
            'descripcion'          => $this->normText($this->input('descripcion')),
            'lugar_de_realizacion' => $this->has('lugar_de_realizacion') ? $this->normText($this->input('lugar_de_realizacion')) : null,
            'justificacion'        => $this->normText($this->input('justificacion')),
            'recursos_necesarios'  => $this->normText($this->input('recursos_necesarios')),
            'requiere_transporte'  => filter_var($this->input('requiere_transporte'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'numero_estudiantes'   => $this->has('numero_estudiantes') ? (int) $this->input('numero_estudiantes') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'creacion_id' => ['required','integer','min:1','exists:creaciones,id'],

            // en arquitectura limpia lo setea el service desde la Creación
            'nombre_practica' => ['prohibited'],

            'descripcion'          => ['required','string'],
            'lugar_de_realizacion' => ['nullable','string','max:255'],
            'justificacion'        => ['required','string'],
            'recursos_necesarios'  => ['required','string'],

            'requiere_transporte' => ['required','boolean'],
            'numero_estudiantes'  => ['required','integer','between:1,100'],

            'fecha_inicio'       => ['required','date'],
            'fecha_finalizacion' => ['required','date','after_or_equal:fecha_inicio'],

            // estados se manejan internamente por el flujo
            'estado_practica'     => ['prohibited'],
            'estado_depart'       => ['prohibited'],
            'estado_postg'        => ['prohibited'],
            'estado_decano'       => ['prohibited'],
            'estado_jefe_postg'   => ['prohibited'],
            'estado_vice'         => ['prohibited'],
        ];
    }
}
