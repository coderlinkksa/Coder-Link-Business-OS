<?php

namespace App\Shared\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasAuditStamps
{
    public static function bootHasAuditStamps(): void
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by ??= auth()->id();
                $model->updated_by ??= auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }

    public function initializeHasAuditStamps(): void
    {
        $this->fillable[] = 'created_by';
        $this->fillable[] = 'updated_by';
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
