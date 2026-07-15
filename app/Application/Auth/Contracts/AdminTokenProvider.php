<?php

namespace App\Application\Auth\Contracts;

use App\Models\User;

interface AdminTokenProvider
{
    public function attempt(string $email, string $password): ?string;

    public function refresh(string $token): string;

    public function user(): ?User;
}
