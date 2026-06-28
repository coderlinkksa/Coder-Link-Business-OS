<?php

namespace App\Modules\Company\API\Requests;

use App\Modules\Company\Domain\Enums\ContactRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'role'       => ['required', 'string', Rule::in(array_column(ContactRole::cases(), 'value'))],
            'email'      => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone'      => ['sometimes', 'nullable', 'string', 'max:50'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }
}
