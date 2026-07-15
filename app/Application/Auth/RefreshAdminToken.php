<?php

namespace App\Application\Auth;

use App\Application\Auth\Contracts\AdminTokenProvider;
use App\Application\Auth\Data\TokenData;

final readonly class RefreshAdminToken
{
    public function __construct(private AdminTokenProvider $tokens) {}

    public function execute(string $token): TokenData
    {
        return new TokenData(
            $this->tokens->refresh($token),
            config('jwt.ttl') * 60,
            config('jwt.refresh_ttl') * 60,
        );
    }
}
