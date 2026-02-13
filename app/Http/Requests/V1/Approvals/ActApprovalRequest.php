<?php

namespace App\Http\Requests\V1\Approval;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\TrimsStrings;

class ActApprovalRequest extends FormRequest
{
    use TrimsStrings;

    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if ($this->has('comment')) {
            $this->merge(['comment' => $this->normText($this->input('comment'))]);
        }
    }

    public function rules(): array
    {
        return [
            'comment' => ['sometimes','nullable','string','max:2000'],
        ];
    }
}
