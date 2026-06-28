<?php

namespace App\Modules\Company\API\Requests;

use App\Modules\Company\Domain\Enums\CompanyStatus;
use App\Modules\Company\Domain\Enums\CompanyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'type'     => ['required', 'string', Rule::in(array_column(CompanyType::cases(), 'value'))],
            'status'   => ['sometimes', 'string', Rule::in(array_column(CompanyStatus::cases(), 'value'))],
            'industry' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone'    => ['sometimes', 'nullable', 'string', 'max:50'],
            'email'    => ['sometimes', 'nullable', 'email', 'max:255'],
            'website'  => ['sometimes', 'nullable', 'url', 'max:2048'],
            'address'  => ['sometimes', 'nullable', 'string', 'max:500'],
            'city'     => ['sometimes', 'nullable', 'string', 'max:100'],
            'country'  => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }
}
