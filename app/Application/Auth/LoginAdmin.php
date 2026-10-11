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
        $tokenData = $this->tokens->attempt($email, $password);

        if ($tokenData === null) {
            throw new InvalidCredentials;
        }

        return $tokenData;
    }
}
