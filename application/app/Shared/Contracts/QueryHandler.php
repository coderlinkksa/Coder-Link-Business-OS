<?php

namespace App\Shared\Contracts;

interface QueryHandler
{
    public function handle(mixed $query): mixed;
}
