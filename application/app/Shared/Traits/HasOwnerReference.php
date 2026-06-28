<?php

namespace App\Shared\Traits;

/**
 * Every business record carries an owner reference field for future
 * multi-tenant isolation (see DATABASE_ARCHITECTURE.md §4).
 * In the single-tenant version this field holds a single constant value.
 */
trait HasOwnerReference
{
    public function initializeHasOwnerReference(): void
    {
        $this->fillable[] = 'owner_id';
    }
}
