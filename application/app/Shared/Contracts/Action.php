<?php

namespace App\Shared\Contracts;

interface Action
{
    public function execute(mixed $data): mixed;
}
