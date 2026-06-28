<?php

namespace App\Modules\CRM\API\Requests;

use App\Modules\CRM\Domain\Enums\LeadSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'              => ['required', 'string', 'max:255'],
            'source'            => ['required', 'string', Rule::in(array_column(LeadSource::cases(), 'value'))],
            'email'             => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone'             => ['sometimes', 'nullable', 'string', 'max:50'],
            'service_requested' => ['sometimes', 'nullable', 'string', 'max:255'],
            'company_id'        => ['sometimes', 'nullable', 'uuid'],
            'contact_person_id' => ['sometimes', 'nullable', 'uuid'],
            'notes'             => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $email = $this->input('email');
            $phone = $this->input('phone');

            if (empty($email) && empty($phone)) {
                $v->errors()->add('email', 'A lead must have at least an email or a phone number.');
            }
        });
    }
}
