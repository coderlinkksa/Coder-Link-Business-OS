<?php

namespace App\Shared\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Wraps Laravel SoftDeletes as a named trait so modules reference
 * a project-level abstraction, not the framework directly.
 */
trait SoftDeletable
{
    use SoftDeletes;
}
