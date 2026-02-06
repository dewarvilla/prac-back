<?php

namespace App\Http\Requests\V1\Salario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalarioRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'anio'  => ['required','integer','digits:4','min:1900','max:3000', Rule::unique('salarios','anio')],
            'valor' => ['required','numeric','min:0'],
        ];
    }
}
