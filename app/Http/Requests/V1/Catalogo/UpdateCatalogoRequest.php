<?php

namespace App\Http\Requests\V1\Catalogo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UpdateCatalogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $trim = fn($s) => preg_replace('/\s+/u', ' ', trim((string)$s));

        $merged = [];
        if ($this->has('facultad'))           $merged['facultad'] = $trim($this->input('facultad'));
        if ($this->has('programa_academico')) $merged['programa_academico'] = $trim($this->input('programa_academico'));
        if ($this->has('nivel_academico'))    $merged['nivel_academico'] = Str::lower((string)$this->input('nivel_academico'));

        if ($merged) {
            $this->merge($merged);
        }
    }

    public function rules(): array
    {
        $base = [
            'nivel_academico'    => [Rule::in(['pregrado','postgrado'])],
            'facultad'           => ['string','max:255'],
            'programa_academico' => ['string','max:255'],
        ];

        $rules = $this->isMethod('patch')
            ? collect($base)->map(fn($r) => array_merge(['sometimes'], $r))->all()
            : [
                'nivel_academico'    => ['required', Rule::in(['pregrado','postgrado'])],
                'facultad'           => ['required','string','max:255'],
                'programa_academico' => ['required','string','max:255'],
            ];

        if ($this->filled('facultad') || $this->filled('programa_academico') || $this->isMethod('put')) {
            $id = $this->route('catalogo')?->id ?? null;

            $rules['programa_academico'][] = Rule::unique('catalogos', 'programa_academico')
                ->where(fn($q) => $q->where(
                    'facultad',
                    $this->input('facultad', optional($this->route('catalogo'))->facultad)
                ))
                ->ignore($id);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'programa_academico.unique' => 'Ya existe ese programa en la facultad indicada.',
        ];
    }
}
