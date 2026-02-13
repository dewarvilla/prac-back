<?php

namespace App\Http\Requests\V1\Creacion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\Concerns\TrimsStrings;

class StoreCreacionRequest extends FormRequest
{
    use TrimsStrings;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'catalogo_id'         => $this->has('catalogo_id') ? (string) $this->input('catalogo_id') : null,
            'nombre_practica'     => $this->normText($this->input('nombre_practica')),
            'recursos_necesarios' => $this->normText($this->input('recursos_necesarios')),
            'justificacion'       => $this->normText($this->input('justificacion')),
        ]);
    }

    public function rules(): array
    {
        return [
            'catalogo_id' => ['required','uuid','exists:catalogos,id'],

            'nombre_practica' => [
                'required','string','max:255',
                Rule::unique('creaciones', 'nombre_practica')
                    ->where(fn($q) => $q->where('catalogo_id', $this->input('catalogo_id'))),
            ],

            'recursos_necesarios' => ['required','string'],
            'justificacion'       => ['required','string'],

            // prohibidos desde cliente
            'estado_creacion'      => ['prohibited'],
            'fechacreacion'        => ['prohibited'],
            'fechamodificacion'    => ['prohibited'],
            'usuariocreacion'      => ['prohibited'],
            'usuariomodificacion'  => ['prohibited'],
            'ipcreacion'           => ['prohibited'],
            'ipmodificacion'       => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_practica.unique' => 'Ya existe una creación con ese nombre en el catálogo indicado.',
        ];
    }
}
