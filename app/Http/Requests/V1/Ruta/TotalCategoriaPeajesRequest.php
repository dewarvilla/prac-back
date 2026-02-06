<?php

namespace App\Http\Requests\V1\Ruta;

use Illuminate\Foundation\Http\FormRequest;

class TotalCategoriaPeajesRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'cat' => ['sometimes','nullable','in:I,II,III,IV,V,VI,VII'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('cat')) {
            $raw = $this->input('cat');
            $this->merge(['cat' => $raw === null ? null : strtoupper(trim((string) $raw))]);
        }
    }
}
