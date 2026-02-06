<?php

namespace App\Http\Requests\V1\Programacion;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProgramacionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $rules = [
            'creacion_id'          => ['uuid','exists:creaciones,id'],
            'nombre_practica'      => ['string','max:255'],
            'descripcion'          => ['string'],
            'lugar_de_realizacion' => ['nullable','string','max:255'],
            'justificacion'        => ['string'],
            'recursos_necesarios'  => ['string'],
            'requiere_transporte'  => ['boolean'],
            'numero_estudiantes'   => ['integer','between:1,100'],

            'estado_practica'      => ['prohibited'],
            'estado_depart'        => ['prohibited'],
            'estado_postg'         => ['prohibited'],
            'estado_decano'        => ['prohibited'],
            'estado_jefe_postg'    => ['prohibited'],
            'estado_vice'          => ['prohibited'],

            'fecha_inicio'         => ['date'],
            'fecha_finalizacion'   => ['date','after_or_equal:fecha_inicio'],
        ];

        if ($this->isMethod('patch')) {
            return collect($rules)->map(fn($r)=>array_merge(['sometimes'], $r))->all();
        }

        return array_merge($rules, [
            'descripcion'         => ['required','string'],
            'justificacion'       => ['required','string'],
            'recursos_necesarios' => ['required','string'],
            'fecha_inicio'        => ['required','date'],
            'fecha_finalizacion'  => ['required','date','after_or_equal:fecha_inicio'],
            'creacion_id'         => ['required','uuid','exists:creaciones,id'],
        ]);
    }
}
