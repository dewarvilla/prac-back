<?php

namespace App\Http\Requests\V1\Creacion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\V1\Creacion\Concerns\NormalizesCreacionInput;

class UpdateCreacionRequest extends FormRequest
{
    use NormalizesCreacionInput;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $merged = [];

        if ($this->has('catalogo_id')) {
            $merged['catalogo_id'] = (int) $this->input('catalogo_id');
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
        $id = $this->route('creacion')?->id;

        // reglas base
        $base = [
            'catalogo_id'         => ['integer','min:1','exists:catalogos,id'],
            'nombre_practica'     => ['string','max:255'],
            'recursos_necesarios' => ['string'],
            'justificacion'       => ['string'],

            // no permitir manipular estados desde cliente
            'estado_creacion'              => ['prohibited'],
            'estado_comite_acreditacion'   => ['prohibited'],
            'estado_consejo_facultad'      => ['prohibited'],
            'estado_consejo_academico'     => ['prohibited'],
            'facultad'                     => ['prohibited'],
            'programa_academico'           => ['prohibited'],
            'nivel_academico'              => ['prohibited'],
        ];

        // si el update toca nombre o catálogo, validamos unicidad por catálogo
        $touchesUnique = $this->filled('nombre_practica') || $this->filled('catalogo_id') || $this->isMethod('put');
        if ($touchesUnique) {
            $catalogoId = $this->input('catalogo_id', $this->route('creacion')?->catalogo_id);

            $base['nombre_practica'][] = Rule::unique('creaciones', 'nombre_practica')
                ->where(fn($q) => $q->where('catalogo_id', $catalogoId))
                ->ignore($id);
        }

        // PATCH: todo optional
        if ($this->isMethod('patch')) {
            return collect($base)->map(fn($r) => array_merge(['sometimes'], $r))->all();
        }

        // PUT: exige todo el cuerpo
        return array_merge($base, [
            'catalogo_id'         => ['required','integer','min:1','exists:catalogos,id'],
            'nombre_practica'     => ['required','string','max:255'],
            'recursos_necesarios' => ['required','string'],
            'justificacion'       => ['required','string'],
        ]);
    }

    public function messages(): array
    {
        return [
            'nombre_practica.unique' => 'Ya existe otra creación con ese nombre en el catálogo indicado.',
        ];
    }
}
