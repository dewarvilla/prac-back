<?php

namespace App\Http\Requests\V1\Catalogo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\Concerns\NormalizesCommon;

class UpdateCatalogoRequest extends FormRequest
{
    use NormalizesCommon;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $merged = [];

        if ($this->has('facultad')) {
            $merged['facultad'] = $this->normText($this->input('facultad'));
        }
        if ($this->has('programa_academico')) {
            $merged['programa_academico'] = $this->normText($this->input('programa_academico'));
        }
        if ($this->has('nivel_academico')) {
            $merged['nivel_academico'] = $this->normLower($this->input('nivel_academico'));
        }

        if ($merged) $this->merge($merged);
    }

    public function rules(): array
    {
        $id = $this->route('catalogo')?->id;

        if ($this->isMethod('patch')) {
            return [
                'nivel_academico'    => ['sometimes', Rule::in(['pregrado','postgrado'])],
                'facultad'           => ['sometimes','string','max:255'],
                'programa_academico' => [
                    'sometimes','string','max:255',
                    Rule::unique('catalogos','programa_academico')
                        ->where(fn($q) => $q->where(
                            'facultad',
                            $this->input('facultad', $this->route('catalogo')?->facultad)
                        ))
                        ->ignore($id),
                ],
            ];
        }

        return [
            'nivel_academico'    => ['required', Rule::in(['pregrado','postgrado'])],
            'facultad'           => ['required','string','max:255'],
            'programa_academico' => [
                'required','string','max:255',
                Rule::unique('catalogos','programa_academico')
                    ->where(fn($q) => $q->where('facultad', $this->input('facultad')))
                    ->ignore($id),
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
