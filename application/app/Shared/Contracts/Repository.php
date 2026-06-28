<?php

namespace App\Shared\Contracts;

interface Repository
{
    public function findById(int|string $id): mixed;

    public function save(mixed $model): void;

    public function delete(int|string $id): bool;
}
