<?php

namespace App\Modules\CRM\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->getKey(),
            'title'             => $this->title,
            'description'       => $this->description,
            'status'            => $this->status->value,
            'priority'          => $this->priority->value,
            'due_at'            => $this->due_at?->toISOString(),
            'completed_at'      => $this->completed_at?->toISOString(),
            'lead_id'           => $this->lead_id,
            'company_id'        => $this->company_id,
            'contact_person_id' => $this->contact_person_id,
            'opportunity_id'    => $this->opportunity_id,
            'assigned_to'       => $this->assigned_to,
            'created_at'        => $this->created_at?->toISOString(),
        ];
    }
}
