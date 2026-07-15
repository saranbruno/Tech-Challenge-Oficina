<?php

namespace App\Application\Auth;

use App\Application\Auth\Contracts\AdminTokenProvider;
use App\Application\Auth\Data\TokenData;
use App\Application\Auth\Exceptions\InvalidCredentials;

final readonly class LoginAdmin
{
    public function __construct(private AdminTokenProvider $tokens) {}

    public function execute(string $email, string $password): TokenData
    {
        $token = $this->tokens->attempt($email, $password);

        if ($token === null) {
            throw new InvalidCredentials;
        }

        return new TokenData(
            $token,
            config('jwt.ttl') * 60,
            config('jwt.refresh_ttl') * 60,
        );
    }
}
