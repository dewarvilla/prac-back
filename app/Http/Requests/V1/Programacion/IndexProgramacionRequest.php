<?php

namespace App\Http\Requests\V1\Programacion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class IndexProgramacionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if ($this->has('sort')) {
            $parts = array_map('trim', explode(',', (string)$this->input('sort')));
            $norm  = array_map(function ($p) {
                $desc  = Str::startsWith($p, '-');
                $field = Str::snake(ltrim($p, '-'));
                return $desc ? "-{$field}" : $field;
            }, $parts);
            $this->merge(['sort' => implode(',', $norm)]);
        }
    }

    public function rules(): array
    {
        $sortable = [
            'id','-id',
            'nombre_practica','-nombre_practica',
            'fecha_inicio','-fecha_inicio',
            'fecha_finalizacion','-fecha_finalizacion',
            'estado_practica','-estado_practica',
            'fechacreacion','-fechacreacion',
            'fechamodificacion','-fechamodificacion',
        ];

        return [
            'q'        => ['sometimes','string','max:255'],
            'per_page' => ['sometimes','integer','min:1','max:200'],
            'page'     => ['sometimes','integer','min:1'],

            'sort'     => ['sometimes', function($attr,$value,$fail) use ($sortable){
                foreach (explode(',', (string)$value) as $p) {
                    $p = trim($p);
                    if ($p === '') continue;
                    if (!in_array($p, $sortable, true)) {
                        return $fail("El valor de sort '{$p}' no es permitido.");
                    }
                }
            }],

            'nombre_practica'     => ['sometimes','string','max:255'],
            'fecha_inicio'        => ['sometimes','date'],
            'fecha_finalizacion'  => ['sometimes','date','after_or_equal:fecha_inicio'],

            'creacion_id'         => ['sometimes','uuid','exists:creaciones,id'],
            'requiere_transporte' => ['sometimes','boolean'],

            'estado_practica'     => ['sometimes', Rule::in([
                'en_aprobacion','aprobada','rechazada','en_ejecucion',
                'ejecutada','en_legalizacion','legalizada'
            ])],
        ];
    }
}
