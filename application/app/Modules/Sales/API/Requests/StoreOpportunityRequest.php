<?php

namespace App\Modules\Sales\API\Requests;

use App\Modules\Sales\Domain\Enums\OpportunityStage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id'          => ['required', 'uuid'],
            'title'               => ['required', 'string', 'max:255'],
            'stage'               => ['sometimes', 'string', Rule::in(array_column(OpportunityStage::cases(), 'value'))],
            'lead_id'             => ['sometimes', 'nullable', 'uuid'],
            'contact_person_id'   => ['sometimes', 'nullable', 'uuid'],
            'value_minor_units'   => ['sometimes', 'nullable', 'integer', 'min:0'],
            'probability'         => ['sometimes', 'nullable', 'integer', 'between:0,100'],
            'expected_close_date' => ['sometimes', 'nullable', 'date'],
            'notes'               => ['sometimes', 'nullable', 'string'],
        ];
    }
}
