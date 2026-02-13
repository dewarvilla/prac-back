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

            'nombre_practica'      => ['prohibited'],
            'nivel_formacion'      => ['prohibited'],
            'estado_practica'      => ['prohibited'],

            'descripcion'          => ['string'],
            'lugar_de_realizacion' => ['nullable','string','max:255'],
            'justificacion'        => ['string'],
            'recursos_necesarios'  => ['string'],
            'requiere_transporte'  => ['boolean'],
            'numero_estudiantes'   => ['integer','between:1,100'],

            'fecha_inicio'         => ['date'],
            'fecha_finalizacion'   => ['date','after_or_equal:fecha_inicio'],

            // auditoría prohibida
            'fechacreacion'        => ['prohibited'],
            'fechamodificacion'    => ['prohibited'],
            'usuariocreacion'      => ['prohibited'],
            'usuariomodificacion'  => ['prohibited'],
            'ipcreacion'           => ['prohibited'],
            'ipmodificacion'       => ['prohibited'],
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
