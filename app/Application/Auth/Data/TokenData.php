<?php

namespace App\Application\Auth\Data;

final readonly class TokenData
{
    public function __construct(
        public string $accessToken,
        public int $expiresIn,
        public int $refreshExpiresIn,
    ) {}
}
