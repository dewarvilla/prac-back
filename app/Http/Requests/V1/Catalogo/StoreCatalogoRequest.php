<?php

namespace App\Http\Requests\V1\Catalogo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\V1\Catalogo\Concerns\NormalizesCatalogoInput;

class StoreCatalogoRequest extends FormRequest
{
    use NormalizesCatalogoInput;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'facultad'           => $this->normText($this->input('facultad')),
            'programa_academico' => $this->normText($this->input('programa_academico')),
            'nivel_academico'    => $this->normNivel($this->input('nivel_academico')),
        ]);
    }

    public function rules(): array
    {
        return [
            'nivel_academico'    => ['required', Rule::in(['pregrado','postgrado'])],
            'facultad'           => ['required','string','max:255'],
            'programa_academico' => [
                'required','string','max:255',
                // UX: evita duplicados por (facultad + programa)
                Rule::unique('catalogos', 'programa_academico')
                    ->where(fn($q) => $q->where('facultad', $this->input('facultad'))),
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
