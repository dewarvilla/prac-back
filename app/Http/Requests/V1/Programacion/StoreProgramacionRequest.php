<?php

namespace App\Http\Requests\V1\Programacion;

use Illuminate\Foundation\Http\FormRequest;

class StoreProgramacionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'creacion_id'          => ['required','uuid','exists:creaciones,id'],
            
            'nombre_practica'      => ['prohibited'],
            'nivel_formacion'      => ['prohibited'],

            'descripcion'          => ['required','string'],
            'lugar_de_realizacion' => ['nullable','string','max:255'],
            'justificacion'        => ['required','string'],
            'recursos_necesarios'  => ['required','string'],
            'requiere_transporte'  => ['required','boolean'],
            'numero_estudiantes'   => ['required','integer','between:1,100'],

            'estado_practica'      => ['prohibited'],

            'fecha_inicio'         => ['required','date'],
            'fecha_finalizacion'   => ['required','date','after_or_equal:fecha_inicio'],

            // auditoría prohibida
            'fechacreacion'        => ['prohibited'],
            'fechamodificacion'    => ['prohibited'],
            'usuariocreacion'      => ['prohibited'],
            'usuariomodificacion'  => ['prohibited'],
            'ipcreacion'           => ['prohibited'],
            'ipmodificacion'       => ['prohibited'],
        ];
    }
}
