<?php

namespace App\Http\Requests\V1\Catalogo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\Concerns\NormalizesCommon;

class BulkCatalogoRequest extends FormRequest
{
    use NormalizesCommon;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if (!$this->has('items')) return;

        $items = $this->input('items', []);
        if (!is_array($items)) $items = [];

        $items = collect($items)->map(function ($i) {
            if (!is_array($i)) return $i;

            return [
            'nivel_academico'    => $this->normLower($i['nivel_academico'] ?? $i['nivelAcademico'] ?? null),
            'facultad'           => $this->normText($i['facultad'] ?? null),
            'programa_academico' => $this->normText($i['programa_academico'] ?? $i['programaAcademico'] ?? null),
        ];
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

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $items = $this->input('items', []);
            if (!is_array($items) || empty($items)) return;

            $keys = [];
            $dupes = [];

            foreach ($items as $idx => $it) {
                $k = $this->normKey($it['facultad'] ?? null, $it['programa_academico'] ?? null);
                if ($k === '|') continue;

                if (isset($keys[$k])) {
                    $dupes[] = [$keys[$k], $idx];
                } else {
                    $keys[$k] = $idx;
                }
            }

            if (!empty($dupes)) {
                $v->errors()->add(
                    'items',
                    'Hay combinaciones repetidas (facultad, programa_academico) dentro del payload.'
                );
            }
        });
    }
}
