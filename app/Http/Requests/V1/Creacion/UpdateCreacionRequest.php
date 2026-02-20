<?php

namespace App\Http\Requests\V1\Creacion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\Concerns\TrimsStrings;
use App\Http\Requests\Concerns\AppliesPatchRules;

class UpdateCreacionRequest extends FormRequest
{
    use TrimsStrings, AppliesPatchRules;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $merged = [];

        if ($this->has('catalogo_id')) {
            $merged['catalogo_id'] = (string) $this->input('catalogo_id');
        }
        if ($this->has('nombre_practica')) {
            $merged['nombre_practica'] = $this->normText($this->input('nombre_practica'));
        }
        if ($this->has('recursos_necesarios')) {
            $merged['recursos_necesarios'] = $this->normText($this->input('recursos_necesarios'));
        }
        if ($this->has('justificacion')) {
            $merged['justificacion'] = $this->normText($this->input('justificacion'));
        }

        if ($merged) $this->merge($merged);
    }

    public function rules(): array
    {
        $creacion = $this->route('creacion');
        $id = $creacion?->id;

        $base = [
            'catalogo_id'         => ['bail','uuid','exists:catalogos,id'],
            'nombre_practica'     => ['bail','string','max:255'],
            'recursos_necesarios' => ['bail','string'],
            'justificacion'       => ['bail','string'],

            // prohibidos desde cliente
            'id'             => ['prohibited'],
            'estado'         => ['prohibited'],
            'created_at'     => ['prohibited'],
            'updated_at'     => ['prohibited'],
            'estado_creacion'=> ['prohibited'],
        ];

        $touchesUnique =
            $this->filled('nombre_practica') ||
            $this->filled('catalogo_id') ||
            $this->isMethod('put');

        if ($touchesUnique) {
            $catalogoId = (string) $this->input('catalogo_id', $creacion?->catalogo_id);

            $base['nombre_practica'][] = Rule::unique('creaciones', 'nombre_practica')
                ->where(fn($q) => $q->where('catalogo_id', $catalogoId))
                ->ignore($id);
        }

        // PATCH
        if ($this->isMethod('patch')) {
            return $this->patchify($base);
        }

        // PUT
        return array_merge($base, [
            'catalogo_id'         => ['bail','required','uuid','exists:catalogos,id'],
            'nombre_practica'     => ['bail','required','string','max:255'],
            'recursos_necesarios' => ['bail','required','string'],
            'justificacion'       => ['bail','required','string'],
        ]);
    }

    public function messages(): array
    {
        return [
            'nombre_practica.unique' => 'Ya existe una práctica con ese nombre en el programa académico indicado.',
        ];
    }
}