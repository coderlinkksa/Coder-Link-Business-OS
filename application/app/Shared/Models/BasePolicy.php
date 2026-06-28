<?php

namespace App\Shared\Models;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

abstract class BasePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user owns the given record.
     * A record is owned when its assigned_to or created_by matches the user.
     */
    protected function owns(User $user, mixed $model): bool
    {
        return isset($model->assigned_to) && $model->assigned_to === $user->id
            || isset($model->created_by) && $model->created_by === $user->id;
    }
}
