<?php

namespace App\Modules\Company\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->getKey(),
            'name'       => $this->name,
            'type'       => $this->type->value,
            'status'     => $this->status->value,
            'industry'   => $this->industry,
            'phone'      => $this->phone,
            'email'      => $this->email,
            'website'    => $this->website,
            'address'    => $this->address,
            'city'       => $this->city,
            'country'    => $this->country,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
