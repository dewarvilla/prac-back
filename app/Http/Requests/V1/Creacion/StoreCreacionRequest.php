<?php

namespace App\Http\Requests\V1\Creacion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\V1\Creacion\Concerns\NormalizesCreacionInput;

class StoreCreacionRequest extends FormRequest
{
    use NormalizesCreacionInput;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'catalogo_id'        => $this->has('catalogo_id') ? (int) $this->input('catalogo_id') : null,
            'nombre_practica'    => $this->normText($this->input('nombre_practica')),
            'recursos_necesarios'=> $this->normText($this->input('recursos_necesarios')),
            'justificacion'      => $this->normText($this->input('justificacion')),
        ]);
    }

    public function rules(): array
    {
        return [
            'catalogo_id' => ['required','integer','min:1','exists:catalogos,id'],

            'nombre_practica' => [
                'required','string','max:255',
                // UX: evita duplicados por catálogo
                Rule::unique('creaciones', 'nombre_practica')
                    ->where(fn($q) => $q->where('catalogo_id', $this->input('catalogo_id'))),
            ],

            'recursos_necesarios' => ['required','string'],
            'justificacion'       => ['required','string'],

            // no permitir que el cliente setee estados
            'estado_creacion'              => ['prohibited'],
            'estado_comite_acreditacion'   => ['prohibited'],
            'estado_consejo_facultad'      => ['prohibited'],
            'estado_consejo_academico'     => ['prohibited'],
            'facultad'                     => ['prohibited'],
            'programa_academico'           => ['prohibited'],
            'nivel_academico'              => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_practica.unique' => 'Ya existe una creación con ese nombre en el catálogo indicado.',
        ];
    }
}
