<?php

namespace App\Http\Requests\V1\Auxilio;

use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteAuxilioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids'   => ['required', 'array', 'min:1', 'max:1000'],
            'ids.*' => ['integer', 'distinct', 'min:1', 'exists:auxilios,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Debes enviar al menos un id.',
        ];
    }
}
