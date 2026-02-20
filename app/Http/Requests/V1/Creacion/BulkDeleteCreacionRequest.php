<?php

namespace App\Http\Requests\V1\Creacion;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\BulkIdsRules;

class BulkDeleteCreacionRequest extends FormRequest
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
        return $this->bulkIdsRules('creaciones', 'uuid');
    }

    public function messages(): array
    {
        return $this->bulkIdsMessages();
    }
}