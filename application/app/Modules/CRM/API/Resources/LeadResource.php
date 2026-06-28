<?php

namespace App\Modules\CRM\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->getKey(),
            'name'              => $this->name,
            'source'            => $this->source->value,
            'status'            => $this->status->value,
            'email'             => $this->email,
            'phone'             => $this->phone,
            'service_requested' => $this->service_requested,
            'company_id'        => $this->company_id,
            'contact_person_id' => $this->contact_person_id,
            'assigned_to'       => $this->assigned_to,
            'notes'             => $this->notes,
            'converted_at'      => $this->converted_at?->toISOString(),
            'created_at'        => $this->created_at?->toISOString(),
        ];
    }
}
