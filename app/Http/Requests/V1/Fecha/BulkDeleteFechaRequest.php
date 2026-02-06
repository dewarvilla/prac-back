<?php

namespace App\Http\Requests\V1\Fecha;

use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteFechaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $ids = $this->input('ids', []);
        $ids = is_array($ids) ? $ids : [];
        $ids = array_values(array_unique(array_map('strval', $ids)));
        $ids = array_values(array_filter($ids, fn($id) => trim($id) !== ''));
        $this->merge(['ids' => $ids]);
    }

    public function rules(): array
    {
        return [
            'ids'   => ['required','array','min:1','max:1000'],
            'ids.*' => ['string','uuid','distinct','exists:fechas,id'],
        ];
    }

    public function messages(): array
    {
        return ['ids.required' => 'Debes enviar al menos un id.'];
    }
}
