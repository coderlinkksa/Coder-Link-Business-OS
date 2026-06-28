<?php

namespace App\Modules\CRM\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConvertLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id'          => ['required', 'uuid'],
            'opportunity_title'   => ['required', 'string', 'max:255'],
            'contact_person_id'   => ['sometimes', 'nullable', 'uuid'],
            'value_minor_units'   => ['sometimes', 'nullable', 'integer', 'min:0'],
            'probability'         => ['sometimes', 'nullable', 'integer', 'between:0,100'],
            'expected_close_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:today'],
            'notes'               => ['sometimes', 'nullable', 'string'],
        ];
    }
}
