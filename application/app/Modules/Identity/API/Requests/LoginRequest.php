<?php

namespace App\Modules\Identity\API\Requests;

use App\Modules\Identity\Application\DTOs\LoginData;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ];
    }

    public function toDTO(): LoginData
    {
        return new LoginData(
            email: $this->string('email')->lower()->value(),
            password: $this->string('password')->value(),
            remember: $this->boolean('remember'),
        );
    }
}
