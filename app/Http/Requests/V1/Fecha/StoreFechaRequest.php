<?php

namespace App\Http\Requests\V1\Fecha;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\PeriodoFechasRule;

class StoreFechaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        // opcional: limpiar periodo
        if ($this->has('periodo')) {
            $this->merge(['periodo' => preg_replace('/\s+/u','', trim((string)$this->input('periodo')))]);
        }
    }

    public function rules(): array
    {
        return [
            'periodo' => ['required','string','regex:/^\d{4}-(1|2)$/','unique:fechas,periodo'],

            'fecha_apertura_preg'            => ['required','date_format:Y-m-d'],
            'fecha_cierre_docente_preg'      => ['required','date_format:Y-m-d','after_or_equal:fecha_apertura_preg'],
            'fecha_cierre_jefe_depart'       => ['required','date_format:Y-m-d','after_or_equal:fecha_cierre_docente_preg'],
            'fecha_cierre_decano'            => ['required','date_format:Y-m-d','after_or_equal:fecha_cierre_jefe_depart'],

            'fecha_apertura_postg'           => ['required','date_format:Y-m-d'],
            'fecha_cierre_docente_postg'     => ['required','date_format:Y-m-d','after_or_equal:fecha_apertura_postg'],
            'fecha_cierre_coordinador_postg' => ['required','date_format:Y-m-d','after_or_equal:fecha_cierre_docente_postg'],
            'fecha_cierre_jefe_postg'        => ['required','date_format:Y-m-d','after_or_equal:fecha_cierre_coordinador_postg'],

            // auditoría/estado prohibidos desde cliente
            'id'                  => ['prohibited'],
            'estado'              => ['prohibited'],
            'fechacreacion'       => ['prohibited'],
            'fechamodificacion'   => ['prohibited'],
            'usuariocreacion'     => ['prohibited'],
            'usuariomodificacion' => ['prohibited'],
            'ipcreacion'          => ['prohibited'],
            'ipmodificacion'      => ['prohibited'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $data = $this->all();
            $rule = new PeriodoFechasRule($data['periodo'] ?? null);

            foreach ([
                'fecha_apertura_preg',
                'fecha_cierre_docente_preg',
                'fecha_cierre_jefe_depart',
                'fecha_cierre_decano',
                'fecha_apertura_postg',
                'fecha_cierre_docente_postg',
                'fecha_cierre_coordinador_postg',
                'fecha_cierre_jefe_postg',
            ] as $campo) {
                if (!isset($data[$campo])) continue;
                if (!$rule->passes($campo, $data[$campo])) {
                    $v->errors()->add($campo, $rule->message());
                }
            }
        });
    }

    public function messages(): array
    {
        return ['periodo.regex' => 'El periodo debe tener el formato YYYY-1 o YYYY-2.'];
    }
}
