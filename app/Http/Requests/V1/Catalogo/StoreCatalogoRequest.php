<?php

namespace App\Http\Requests\V1\Catalogo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\Concerns\TrimsStrings;

class StoreCatalogoRequest extends FormRequest
{
    use TrimsStrings;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'facultad'           => $this->normText($this->input('facultad')),
            'programa_academico' => $this->normText($this->input('programa_academico')),
            'nivel_academico'    => $this->normText($this->input('nivel_academico')),
        ]);
    }

    public function rules(): array
    {
        return [
            'nivel_academico'    => ['bail','required', Rule::in(['pregrado','postgrado'])],
            'facultad'           => ['bail','required','string','max:255'],
            'programa_academico' => [
                'bail','required','string','max:255',
                Rule::unique('catalogos', 'programa_academico')
                    ->where(fn($q) => $q->where('facultad', $this->input('facultad'))),
            ],

            // prohibidos desde cliente
            'id'         => ['prohibited'],
            'estado'     => ['prohibited'],
            'created_at' => ['prohibited'],
            'updated_at' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'programa_academico.unique' => 'Ya existe ese programa en la facultad indicada.',
        ];
    }
}