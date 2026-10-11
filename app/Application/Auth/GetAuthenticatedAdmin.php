<?php

namespace App\Application\Auth;

use App\Application\Auth\Contracts\AdminTokenProvider;
use App\Application\Auth\Data\AuthenticatedAdminData;

final readonly class GetAuthenticatedAdmin
{
    public function __construct(private AdminTokenProvider $tokens) {}

    public function execute(): ?AuthenticatedAdminData
    {
        return $this->tokens->user();
    }
}
