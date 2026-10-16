<?php

namespace App\Application\Auth\Contracts;

use App\Application\Auth\Data\AuthenticatedAdminData;
use App\Application\Auth\Data\TokenData;

interface AdminTokenProvider
{
    public function attempt(string $email, string $password): ?TokenData;

    public function refresh(string $token): TokenData;

    public function user(): ?AuthenticatedAdminData;
}
