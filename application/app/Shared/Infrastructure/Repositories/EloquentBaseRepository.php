<?php

namespace App\Shared\Infrastructure\Repositories;

use App\Shared\Contracts\Repository;
use Illuminate\Database\Eloquent\Model;

/**
 * Abstract Eloquent implementation of the Repository contract.
 * Module repositories MAY extend this for standard CRUD; they
 * are not required to do so when their contract differs significantly.
 */
abstract class EloquentBaseRepository implements Repository
{
    /** @return class-string<Model> */
    abstract protected function modelClass(): string;

    public function findById(int|string $id): ?Model
    {
        return ($this->modelClass())::find($id);
    }

    public function save(mixed $model): void
    {
        $model->save();
    }

    public function delete(int|string $id): bool
    {
        $instance = $this->findById($id);

        return $instance?->delete() ?? false;
    }

    public function findOrFail(int|string $id): Model
    {
        return ($this->modelClass())::findOrFail($id);
    }
}
