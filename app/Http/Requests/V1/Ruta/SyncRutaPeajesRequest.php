<?php

namespace App\Http\Requests\V1\Ruta;

use Illuminate\Foundation\Http\FormRequest;

class SyncRutaPeajesRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'categoria' => ['sometimes','in:I,II,III,IV,V,VI,VII'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('categoria')) {
            $this->merge(['categoria' => strtoupper(trim((string) $this->input('categoria')))]);
        }
    }
}
