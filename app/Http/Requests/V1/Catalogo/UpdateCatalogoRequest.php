<?php

namespace App\Http\Requests\V1\Catalogo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\Concerns\TrimsStrings;
use App\Http\Requests\Concerns\AppliesPatchRules;

class UpdateCatalogoRequest extends FormRequest
{
    use TrimsStrings, AppliesPatchRules;

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
            $merged['nivel_academico'] = $this->normText($this->input('nivel_academico'));
        }

        if ($merged) $this->merge($merged);
    }

    public function rules(): array
    {
        $catalogo = $this->route('catalogo');
        $id = $catalogo?->id;

        $base = [
            'nivel_academico'    => ['bail', Rule::in(['pregrado','postgrado'])],
            'facultad'           => ['bail','string','max:255'],
            'programa_academico' => ['bail','string','max:255'],

            // prohibidos desde cliente
            'id'         => ['prohibited'],
            'estado'     => ['prohibited'],
            'created_at' => ['prohibited'],
            'updated_at' => ['prohibited'],
        ];

        $touchesUnique =
            $this->filled('facultad') ||
            $this->filled('programa_academico') ||
            $this->isMethod('put');

        if ($touchesUnique) {
            $facultad = $this->input('facultad', $catalogo?->facultad);

            $base['programa_academico'][] = Rule::unique('catalogos','programa_academico')
                ->where(fn($q) => $q->where('facultad', $facultad))
                ->ignore($id);
        }

        if ($this->isMethod('patch')) {
            return $this->patchify($base);
        }

        return array_merge($base, [
            'nivel_academico'    => ['bail','required', Rule::in(['pregrado','postgrado'])],
            'facultad'           => ['bail','required','string','max:255'],
            'programa_academico' => ['bail','required','string','max:255'],
        ]);
    }

    public function messages(): array
    {
        return [
            'programa_academico.unique' => 'Ya existe ese programa en la facultad indicada.',
        ];
    }
}