<?php

namespace App\Http\Requests\V1\Auxilio;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAuxilioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $money = function ($v) {
            if ($v === null) return null;
            if (is_numeric($v)) return $v;
            $v = trim((string) $v);
            if ($v === '') return null;
            $v = str_replace([' ', ','], '', $v);
            return is_numeric($v) ? $v : null;
        };

        $merge = [];
        foreach (['valor_por_docente','valor_por_estudiante','valor_por_acompanante'] as $k) {
            if ($this->has($k)) $merge[$k] = $money($this->input($k));
        }

        if ($merge) $this->merge($merge);
    }

    public function rules(): array
    {
        $base = [
            'pernocta'              => ['boolean'],
            'distancias_mayor_70km' => ['boolean'],
            'fuera_cordoba'         => ['boolean'],

            'valor_por_docente'     => ['numeric', 'min:0'],
            'valor_por_estudiante'  => ['numeric', 'min:0'],
            'valor_por_acompanante' => ['numeric', 'min:0'],

            'programacion_id'       => ['integer', 'exists:programaciones,id'],
        ];

        if ($this->isMethod('patch')) {
            return collect($base)->map(fn ($r) => array_merge(['sometimes'], $r))->all();
        }

        // PUT
        return [
            'pernocta'              => ['required', 'boolean'],
            'distancias_mayor_70km' => ['required', 'boolean'],
            'fuera_cordoba'         => ['required', 'boolean'],

            'valor_por_docente'     => ['required', 'numeric', 'min:0'],
            'valor_por_estudiante'  => ['required', 'numeric', 'min:0'],
            'valor_por_acompanante' => ['required', 'numeric', 'min:0'],

            // Si en tu dominio NO es obligatorio, cámbialo a sometimes
            'programacion_id'       => ['required', 'integer', 'exists:programaciones,id'],
        ];
    }
}
