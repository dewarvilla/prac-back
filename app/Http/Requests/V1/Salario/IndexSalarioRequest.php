<?php

namespace App\Http\Requests\V1\Salario;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\NormalizesSort;
use App\Http\Requests\Concerns\TrimsStrings;

class IndexSalarioRequest extends FormRequest
{
    use NormalizesSort, TrimsStrings;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if ($this->has('q')) $this->merge(['q' => $this->normText($this->input('q'))]);
        $this->normalizeSortInput('sort');
    }

    public function rules(): array
    {
        $sortable = [
            'id','-id',
            'anio','-anio',
            'valor','-valor',
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

            'anio'  => ['sometimes','integer','digits:4','min:1900','max:3000'],
            'valor' => ['sometimes','numeric','min:0'],
            'estado' => ['sometimes','boolean'],
        ];
    }
}
