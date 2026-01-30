<?php

namespace App\Http\Requests\V1\Catalogo;

use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteCatalogoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $ids = $this->input('ids', []);
        $ids = is_array($ids) ? $ids : [];

        $ids = array_values(array_unique(array_map('intval', $ids)));
        $ids = array_values(array_filter($ids, fn($id) => $id > 0));

        $this->merge(['ids' => $ids]);
    }

    public function rules(): array
    {
        return [
            'ids'   => ['required','array','min:1','max:500'],
            'ids.*' => ['integer','min:1'],
        ];
    }
}
