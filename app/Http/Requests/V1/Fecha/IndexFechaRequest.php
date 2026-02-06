<?php

namespace App\Http\Requests\V1\Fecha;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\NormalizesSort;
use App\Http\Requests\Concerns\TrimsStrings;

class IndexFechaRequest extends FormRequest
{
    use NormalizesSort, TrimsStrings;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if ($this->has('q')) $this->merge(['q' => $this->normText($this->input('q'))]);
        if ($this->has('periodo')) $this->merge(['periodo' => $this->normText($this->input('periodo'))]);
        if ($this->has('periodo.lk')) $this->merge(['periodo.lk' => $this->normText($this->input('periodo.lk'))]);

        $this->normalizeSortInput('sort');
    }

    public function rules(): array
    {
        $sortable = [
            'id','-id','periodo','-periodo',
            'fecha_apertura_preg','-fecha_apertura_preg',
            'fecha_cierre_docente_preg','-fecha_cierre_docente_preg',
            'fecha_apertura_postg','-fecha_apertura_postg',
            'fecha_cierre_docente_postg','-fecha_cierre_docente_postg',
            'fechacreacion','-fechacreacion',
            'fechamodificacion','-fechamodificacion',
        ];

        return [
            'q'        => ['sometimes','nullable','string','max:255'],
            'per_page' => ['sometimes','integer','min:1','max:200'],
            'page'     => ['sometimes','integer','min:1'],

            'sort' => ['sometimes', function($attr,$value,$fail) use ($sortable){
                foreach (explode(',', (string)$value) as $p) {
                    $p = trim($p);
                    if ($p === '') continue;
                    if (!in_array($p, $sortable, true)) {
                        return $fail("El valor de sort '{$p}' no es permitido.");
                    }
                }
            }],

            'periodo'    => ['sometimes','string','max:20'],
            'periodo.lk' => ['sometimes','string','max:20'],

            'fecha_apertura_preg'            => ['sometimes','date'],
            'fecha_cierre_docente_preg'      => ['sometimes','date'],
            'fecha_cierre_jefe_depart'       => ['sometimes','date'],
            'fecha_cierre_decano'            => ['sometimes','date'],
            'fecha_apertura_postg'           => ['sometimes','date'],
            'fecha_cierre_docente_postg'     => ['sometimes','date'],
            'fecha_cierre_coordinador_postg' => ['sometimes','date'],
            'fecha_cierre_jefe_postg'        => ['sometimes','date'],
        ];
    }
}
