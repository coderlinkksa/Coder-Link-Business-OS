<?php

namespace App\Modules\CRM\API\Requests;

use App\Modules\CRM\Domain\Enums\TaskPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'             => ['required', 'string', 'max:255'],
            'description'       => ['sometimes', 'nullable', 'string'],
            'priority'          => ['sometimes', 'string', Rule::in(array_column(TaskPriority::cases(), 'value'))],
            'due_at'            => ['sometimes', 'nullable', 'date_format:Y-m-d H:i:s'],
            'lead_id'           => ['sometimes', 'nullable', 'uuid'],
            'company_id'        => ['sometimes', 'nullable', 'uuid'],
            'contact_person_id' => ['sometimes', 'nullable', 'uuid'],
            'opportunity_id'    => ['sometimes', 'nullable', 'uuid'],
            'assigned_to'       => ['sometimes', 'nullable', 'uuid'],
        ];
    }
}
