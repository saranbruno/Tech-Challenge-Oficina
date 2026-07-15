<?php

namespace App\Infrastructure\Auth;

use App\Application\Auth\Contracts\AdminTokenProvider;
use App\Application\Auth\Exceptions\InvalidRefreshToken;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

final readonly class JwtAdminTokenProvider implements AdminTokenProvider
{
    public function __construct(private Factory $auth) {}

    public function attempt(string $email, string $password): ?string
    {
        $token = $this->auth->guard('api')->attempt([
            'email' => $email,
            'password' => $password,
        ]);

        return $token === false ? null : $token;
    }

    public function refresh(string $token): string
    {
        try {
            return $this->auth->guard('api')->setToken($token)->refresh();
        } catch (JWTException) {
            throw new InvalidRefreshToken;
        }
    }

    public function user(): ?User
    {
        $user = $this->auth->guard('api')->user();

        return $user instanceof User ? $user : null;
    }
}
