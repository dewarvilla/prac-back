<?php

namespace App\Http\Requests\V1\Catalogo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkCatalogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('items')) return;

        $trim = fn($s) => preg_replace('/\s+/u', ' ', trim((string)$s));

        $items = collect($this->input('items', []))->map(function ($i) use ($trim) {
            if (is_array($i)) {
                if (isset($i['facultad']))           $i['facultad'] = $trim($i['facultad']);
                if (isset($i['programa_academico'])) $i['programa_academico'] = $trim($i['programa_academico']);
                if (isset($i['nivel_academico']))    $i['nivel_academico'] = mb_strtolower((string)$i['nivel_academico']);
            }
            return $i;
        })->all();

        $this->merge(['items' => $items]);
    }

    public function rules(): array
    {
        return [
            'items'                      => ['required','array','min:1','max:1000'],
            'items.*'                    => ['required','array'],
            'items.*.nivel_academico'    => ['required', Rule::in(['pregrado','postgrado'])],
            'items.*.facultad'           => ['required','string','max:255'],
            'items.*.programa_academico' => ['required','string','max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Debes enviar al menos un elemento.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $items = collect($this->input('items', []));

            $norm = function (?string $s): string {
                $s = (string) $s;
                $s = preg_replace('/\s+/u', ' ', trim($s));
                return mb_strtolower($s);
            };

            $dupes = $items->groupBy(function ($i) use ($norm) {
                $fac = $norm($i['facultad'] ?? '');
                $pro = $norm($i['programa_academico'] ?? '');
                return $fac.'|'.$pro;
            })->filter(fn($g) => $g->count() > 1)->keys();

            if ($dupes->isNotEmpty()) {
                $v->errors()->add(
                    'items',
                    'Hay combinaciones repetidas (facultad, programa_académico) en el payload.'
                );
            }
        });
    }
}
