<?php

namespace App\Modules\CRM\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->getKey(),
            'type'              => $this->type->value,
            'subject'           => $this->subject,
            'body'              => $this->body,
            'occurred_at'       => $this->occurred_at?->toISOString(),
            'lead_id'           => $this->lead_id,
            'company_id'        => $this->company_id,
            'contact_person_id' => $this->contact_person_id,
            'opportunity_id'    => $this->opportunity_id,
            'created_at'        => $this->created_at?->toISOString(),
        ];
    }
}
