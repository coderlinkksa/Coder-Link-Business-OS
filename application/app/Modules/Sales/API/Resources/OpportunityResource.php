<?php

namespace App\Modules\Sales\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpportunityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->getKey(),
            'company_id'          => $this->company_id,
            'lead_id'             => $this->lead_id,
            'contact_person_id'   => $this->contact_person_id,
            'title'               => $this->title,
            'stage'               => $this->stage->value,
            'value_minor_units'   => $this->value_minor_units,
            'probability'         => $this->probability,
            'expected_close_date' => $this->expected_close_date?->format('Y-m-d'),
            'loss_reason'         => $this->loss_reason,
            'assigned_to'         => $this->assigned_to,
            'notes'               => $this->notes,
            'created_at'          => $this->created_at?->toISOString(),
        ];
    }
}
