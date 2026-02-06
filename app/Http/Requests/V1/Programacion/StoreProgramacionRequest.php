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

            'nombre_practica'      => ['nullable','string','max:255'], // lo sobreescribimos desde Creación
            'descripcion'          => ['required','string'],
            'lugar_de_realizacion' => ['nullable','string','max:255'],
            'justificacion'        => ['required','string'],
            'recursos_necesarios'  => ['required','string'],
            'requiere_transporte'  => ['required','boolean'],
            'numero_estudiantes'   => ['required','integer','between:1,100'],

            'estado_practica'      => ['prohibited'],
            'estado_depart'        => ['prohibited'],
            'estado_postg'         => ['prohibited'],
            'estado_decano'        => ['prohibited'],
            'estado_jefe_postg'    => ['prohibited'],
            'estado_vice'          => ['prohibited'],

            'fecha_inicio'         => ['required','date'],
            'fecha_finalizacion'   => ['required','date','after_or_equal:fecha_inicio'],
        ];
    }
}
