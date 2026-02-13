<?php

namespace App\Http\Requests\V1\Salario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalarioRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if ($this->has('anio'))  $this->merge(['anio'  => (int) $this->input('anio')]);
        if ($this->has('valor')) $this->merge(['valor' => (float) $this->input('valor')]);
    }

    public function rules(): array
    {
        return [
            'anio'  => ['required','integer','digits:4','min:1900','max:3000', Rule::unique('salarios','anio')],
            'valor' => ['required','numeric','min:0'],

            // prohibidos desde cliente
            'id'                => ['prohibited'],
            'estado'            => ['prohibited'],
            'fechacreacion'     => ['prohibited'],
            'fechamodificacion' => ['prohibited'],
            'usuariocreacion'   => ['prohibited'],
            'usuariomodificacion'=> ['prohibited'],
            'ipcreacion'        => ['prohibited'],
            'ipmodificacion'    => ['prohibited'],
        ];
    }
}
