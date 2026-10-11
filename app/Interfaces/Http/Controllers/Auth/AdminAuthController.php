<?php

namespace App\Interfaces\Http\Controllers\Auth;

use App\Application\Auth\GetAuthenticatedAdmin;
use App\Application\Auth\LoginAdmin;
use App\Application\Auth\RefreshAdminToken;
use App\Interfaces\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAuthController
{
    public function login(LoginRequest $request, LoginAdmin $login): JsonResponse
    {
        $data = $login->execute(
            $request->string('email')->toString(),
            $request->string('password')->toString(),
        );

        return response()->json([
            'access_token' => $data->accessToken,
            'token_type' => 'bearer',
            'expires_in' => $data->expiresIn,
            'refresh_expires_in' => $data->refreshExpiresIn,
        ]);
    }

    public function refresh(Request $request, RefreshAdminToken $refresh): JsonResponse
    {
        $data = $refresh->execute((string) $request->bearerToken());

        return response()->json([
            'access_token' => $data->accessToken,
            'token_type' => 'bearer',
            'expires_in' => $data->expiresIn,
            'refresh_expires_in' => $data->refreshExpiresIn,
        ]);
    }

    public function me(GetAuthenticatedAdmin $getAuthenticatedAdmin): JsonResponse
    {
        $admin = $getAuthenticatedAdmin->execute();

        return response()->json([
            'data' => [
                'id' => $admin?->id,
                'name' => $admin?->name,
                'email' => $admin?->email,
            ],
        ]);
    }
}
