<?php

namespace App\Interfaces\Http\Middleware;

use App\Interfaces\Http\Responses\ApiErrorResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class VerifyServiceOrderWebhookSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.service_order_webhook.secret');
        $signature = $request->header('X-Webhook-Signature');

        if (! is_string($secret) || $secret === '' || ! is_string($signature) || $signature === '') {
            return ApiErrorResponse::make(
                'invalid_webhook_signature',
                'Assinatura do webhook ausente ou invalida.',
                401,
            );
        }

        $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $secret);

        if (! hash_equals($expected, trim($signature))) {
            return ApiErrorResponse::make(
                'invalid_webhook_signature',
                'Assinatura do webhook ausente ou invalida.',
                401,
            );
        }

        return $next($request);
    }
}
