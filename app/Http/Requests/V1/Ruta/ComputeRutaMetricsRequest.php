<?php

namespace App\Http\Requests\V1\Ruta;

use Illuminate\Foundation\Http\FormRequest;

class ComputeRutaMetricsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'mode' => ['sometimes','in:DRIVE,BICYCLE,WALK,TRANSIT,TWO_WHEELER'],
        ];
    }
}
