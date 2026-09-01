<?php

namespace App\Application\Auth\Data;

final readonly class AuthenticatedAdminData
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
    ) {}
}
