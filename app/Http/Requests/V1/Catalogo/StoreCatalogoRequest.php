<?php

namespace App\Http\Requests\V1\Catalogo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class StoreCatalogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $trim = fn($s) => preg_replace('/\s+/u', ' ', trim((string)$s));

        $this->merge([
            'facultad'           => $trim($this->input('facultad')),
            'programa_academico' => $trim($this->input('programa_academico')),
            'nivel_academico'    => Str::lower((string)$this->input('nivel_academico')),
        ]);
    }

    public function rules(): array
    {
        return [
            'nivel_academico'    => ['required', Rule::in(['pregrado','postgrado'])],
            'facultad'           => ['required','string','max:255'],
            'programa_academico' => [
                'required','string','max:255',
                Rule::unique('catalogos', 'programa_academico')
                    ->where(fn($q) => $q->where('facultad', $this->facultad))
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'programa_academico.unique' => 'Ya existe ese programa en la facultad indicada.',
        ];
    }
}
