<?php

namespace App\Shared\Contracts;

interface Repository
{
    public function findById(int $id): mixed;

    public function save(mixed $model): mixed;

    public function delete(int $id): bool;
}
