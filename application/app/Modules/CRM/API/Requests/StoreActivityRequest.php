<?php

namespace App\Modules\CRM\API\Requests;

use App\Modules\CRM\Domain\Enums\ActivityType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'              => ['required', 'string', Rule::in(array_column(ActivityType::cases(), 'value'))],
            'subject'           => ['required', 'string', 'max:255'],
            'body'              => ['sometimes', 'nullable', 'string'],
            'occurred_at'       => ['sometimes', 'nullable', 'date_format:Y-m-d H:i:s'],
            'lead_id'           => ['sometimes', 'nullable', 'uuid'],
            'company_id'        => ['sometimes', 'nullable', 'uuid'],
            'contact_person_id' => ['sometimes', 'nullable', 'uuid'],
            'opportunity_id'    => ['sometimes', 'nullable', 'uuid'],
        ];
    }
}
