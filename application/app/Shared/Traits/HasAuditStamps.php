<?php

namespace App\Shared\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasAuditStamps
{
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
