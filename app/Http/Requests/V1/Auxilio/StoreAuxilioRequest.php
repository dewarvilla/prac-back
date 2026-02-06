<?php

namespace App\Http\Requests\V1\Auxilio;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuxilioRequest extends FormRequest
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

            // Permite "1.234.567" o "1,234,567" -> 1234567
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
        return [
            'pernocta'              => ['required', 'boolean'],
            'distancias_mayor_70km' => ['required', 'boolean'],
            'fuera_cordoba'         => ['required', 'boolean'],

            'valor_por_docente'     => ['nullable', 'numeric', 'min:0'],
            'valor_por_estudiante'  => ['nullable', 'numeric', 'min:0'],
            'valor_por_acompanante' => ['nullable', 'numeric', 'min:0'],

            // Si en tu dominio es obligatorio, cambia a: ['required','integer','exists:programaciones,id']
            'programacion_id'       => ['sometimes', 'integer', 'exists:programaciones,id'],
        ];
    }
}
