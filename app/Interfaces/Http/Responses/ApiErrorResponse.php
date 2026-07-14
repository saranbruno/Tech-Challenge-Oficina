<?php

namespace App\Interfaces\Http\Responses;

use Illuminate\Http\JsonResponse;

final class ApiErrorResponse
{
    public static function make(
        string $code,
        string $message,
        int $status,
        array $details = [],
    ): JsonResponse {
        $error = [
            'code' => $code,
            'message' => $message,
        ];

        if ($details !== []) {
            $error['details'] = $details;
        }

        return response()->json(['error' => $error], $status);
    }
}
