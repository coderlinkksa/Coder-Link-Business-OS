<?php

namespace App\Modules\Company\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->getKey(),
            'company_id' => $this->company_id,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'full_name'  => $this->fullName(),
            'role'       => $this->role->value,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'is_primary' => $this->is_primary,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
