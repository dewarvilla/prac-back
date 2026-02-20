<?php

namespace App\Http\Requests\V1\Catalogo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\Concerns\TrimsStrings;

class BulkCatalogoRequest extends FormRequest
{
    use TrimsStrings;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $items = $this->input('items', []);
        if (!is_array($items)) $items = [];

        $items = collect($items)->map(function ($i) {
            if (!is_array($i)) return $i;

            return [
                'nivel_academico'    => $this->normText($i['nivel_academico'] ?? $i['nivelAcademico'] ?? null),
                'facultad'           => $this->normText($i['facultad'] ?? null),
                'programa_academico' => $this->normText($i['programa_academico'] ?? $i['programaAcademico'] ?? null),
            ];
        })->all();

        $this->merge(['items' => $items]);
    }

    public function rules(): array
    {
        return [
            'items'                      => ['required', 'array', 'min:1', 'max:1000'],
            'items.*'                    => ['required', 'array'],
            'items.*.nivel_academico'    => ['required', Rule::in(['pregrado', 'postgrado'])],
            'items.*.facultad'           => ['required', 'string', 'max:255'],
            'items.*.programa_academico' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $items = $this->input('items', []);
            if (!is_array($items) || empty($items)) return;

            $seen = [];
            foreach ($items as $idx => $it) {
                if (!is_array($it)) continue;

                $k = mb_strtolower(trim((string)($it['facultad'] ?? ''))).'|'.mb_strtolower(trim((string)($it['programa_academico'] ?? '')));
                if ($k === '|') continue;

                if (isset($seen[$k])) {
                    $v->errors()->add('items', 'Hay combinaciones repetidas (facultad, programa_academico) dentro del payload.');
                    return;
                }
                $seen[$k] = $idx;
            }
        });
    }
}