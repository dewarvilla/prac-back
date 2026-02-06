<?php

namespace App\Http\Requests\V1\Fecha;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\AppliesPatchRules;
use App\Rules\PeriodoFechasRule;

class UpdateFechaRequest extends FormRequest
{
    use AppliesPatchRules;

    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('fecha')?->id;

        $rules = [
            'periodo' => ['string','regex:/^\d{4}-(1|2)$/',"unique:fechas,periodo,{$id}"],

            'fecha_apertura_preg'            => ['date_format:Y-m-d'],
            'fecha_cierre_docente_preg'      => ['date_format:Y-m-d','after_or_equal:fecha_apertura_preg'],
            'fecha_cierre_jefe_depart'       => ['date_format:Y-m-d','after_or_equal:fecha_cierre_docente_preg'],
            'fecha_cierre_decano'            => ['date_format:Y-m-d','after_or_equal:fecha_cierre_jefe_depart'],

            'fecha_apertura_postg'           => ['date_format:Y-m-d'],
            'fecha_cierre_docente_postg'     => ['date_format:Y-m-d','after_or_equal:fecha_apertura_postg'],
            'fecha_cierre_coordinador_postg' => ['date_format:Y-m-d','after_or_equal:fecha_cierre_docente_postg'],
            'fecha_cierre_jefe_postg'        => ['date_format:Y-m-d','after_or_equal:fecha_cierre_coordinador_postg'],

            // prohibidos desde cliente
            'estado'              => ['prohibited'],
            'fechacreacion'       => ['prohibited'],
            'fechamodificacion'   => ['prohibited'],
            'usuariocreacion'     => ['prohibited'],
            'usuariomodificacion' => ['prohibited'],
            'ipcreacion'          => ['prohibited'],
            'ipmodificacion'      => ['prohibited'],
        ];

        if ($this->isMethod('put')) {
            return array_merge($rules, [
                'periodo' => ['required','string','regex:/^\d{4}-(1|2)$/',"unique:fechas,periodo,{$id}"],

                'fecha_apertura_preg'            => ['required','date_format:Y-m-d'],
                'fecha_cierre_docente_preg'      => ['required','date_format:Y-m-d','after_or_equal:fecha_apertura_preg'],
                'fecha_cierre_jefe_depart'       => ['required','date_format:Y-m-d','after_or_equal:fecha_cierre_docente_preg'],
                'fecha_cierre_decano'            => ['required','date_format:Y-m-d','after_or_equal:fecha_cierre_jefe_depart'],

                'fecha_apertura_postg'           => ['required','date_format:Y-m-d'],
                'fecha_cierre_docente_postg'     => ['required','date_format:Y-m-d','after_or_equal:fecha_apertura_postg'],
                'fecha_cierre_coordinador_postg' => ['required','date_format:Y-m-d','after_or_equal:fecha_cierre_docente_postg'],
                'fecha_cierre_jefe_postg'        => ['required','date_format:Y-m-d','after_or_equal:fecha_cierre_coordinador_postg'],
            ]);
        }

        return $this->patchify($rules);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $periodo = $this->input('periodo', $this->route('fecha')?->periodo);
            if (!$periodo) return;

            $rule = new PeriodoFechasRule($periodo);

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
                if ($this->filled($campo) && !$rule->passes($campo, $this->input($campo))) {
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
