<?php

namespace App\Infrastructure\Auth;

use App\Application\Auth\Contracts\AdminTokenProvider;
use App\Application\Auth\Data\AuthenticatedAdminData;
use App\Application\Auth\Data\TokenData;
use App\Application\Auth\Exceptions\InvalidRefreshToken;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

final readonly class JwtAdminTokenProvider implements AdminTokenProvider
{
    public function __construct(private Factory $auth) {}

    public function attempt(string $email, string $password): ?TokenData
    {
        $token = $this->auth->guard('api')->attempt([
            'email' => $email,
            'password' => $password,
        ]);

        return $token === false ? null : $this->tokenData($token);
    }

    public function refresh(string $token): TokenData
    {
        try {
            return $this->tokenData($this->auth->guard('api')->setToken($token)->refresh());
        } catch (JWTException) {
            throw new InvalidRefreshToken;
        }
    }

    public function user(): ?AuthenticatedAdminData
    {
        $user = $this->auth->guard('api')->user();

        return $user instanceof User
            ? new AuthenticatedAdminData((int) $user->getKey(), $user->name, $user->email)
            : null;
    }

    private function tokenData(string $token): TokenData
    {
        return new TokenData(
            $token,
            (int) config('jwt.ttl') * 60,
            (int) config('jwt.refresh_ttl') * 60,
        );
    }
}
