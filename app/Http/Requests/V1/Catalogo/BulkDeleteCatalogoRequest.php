<?php

namespace App\Http\Requests\V1\Catalogo;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\BulkIdsRules;

class BulkDeleteCatalogoRequest extends FormRequest
{
    use BulkIdsRules;

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
        return $this->bulkIdsRules('catalogos', 'uuid', 500);
    }

    public function messages(): array
    {
        return $this->bulkIdsMessages();
    }
}