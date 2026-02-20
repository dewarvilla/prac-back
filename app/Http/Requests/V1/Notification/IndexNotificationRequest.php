<?php

namespace App\Http\Requests\V1\Notification;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\NormalizesSort;

class IndexNotificationRequest extends FormRequest
{
    use NormalizesSort;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->normalizeSortInput('sort');

        if ($this->has('unread')) {
            $this->merge([
                'unread' => filter_var($this->input('unread'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    public function rules(): array
    {
        $sortable = ['-created_at','created_at','-read_at','read_at'];

        return [
            'per_page' => ['sometimes','integer','min:1','max:200'],
            'page'     => ['sometimes','integer','min:1'],
            'unread'   => ['sometimes','boolean'],
            'sort'     => ['sometimes', function($attr,$value,$fail) use ($sortable){
                foreach (explode(',', (string)$value) as $p) {
                    $p = trim($p);
                    if ($p === '') continue;
                    if (!in_array($p, $sortable, true)) return $fail("El valor de sort '{$p}' no es permitido.");
                }
            }],
        ];
    }
}
