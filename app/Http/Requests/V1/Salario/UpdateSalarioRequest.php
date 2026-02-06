<?php

namespace App\Http\Requests\V1\Salario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\Concerns\AppliesPatchRules;

class UpdateSalarioRequest extends FormRequest
{
    use AppliesPatchRules;

    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('salario')?->id;

        $rules = [
            'anio'  => ['integer','digits:4','min:1900','max:3000', Rule::unique('salarios','anio')->ignore($id)],
            'valor' => ['numeric','min:0'],
        ];

        if ($this->isMethod('put')) {
            return [
                'anio'  => ['required','integer','digits:4','min:1900','max:3000', Rule::unique('salarios','anio')->ignore($id)],
                'valor' => ['required','numeric','min:0'],
            ];
        }

        return $this->patchify($rules);
    }
}
