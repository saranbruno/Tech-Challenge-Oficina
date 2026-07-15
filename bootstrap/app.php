<?php

use App\Application\Auth\Exceptions\InvalidCredentials;
use App\Application\Auth\Exceptions\InvalidRefreshToken;
use App\Interfaces\Http\Responses\ApiErrorResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {})
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiErrorResponse::make(
                'validation_error',
                'Os dados informados sao invalidos.',
                422,
                $exception->errors(),
            );
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return ApiErrorResponse::make(
                'unauthenticated',
                'Autenticacao necessaria.',
                401,
            );
        });

        $exceptions->render(function (InvalidCredentials $exception, Request $request) {
            return ApiErrorResponse::make(
                'invalid_credentials',
                'Credenciais invalidas.',
                401,
            );
        });

        $exceptions->render(function (InvalidRefreshToken $exception, Request $request) {
            return ApiErrorResponse::make(
                'invalid_token',
                'Token ausente, invalido ou fora da janela de renovacao.',
                401,
            );
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = $exception->getStatusCode();
            $message = $exception->getMessage() ?: Response::$statusTexts[$status] ?? 'Erro HTTP.';

            return ApiErrorResponse::make(
                match ($status) {
                    404 => 'not_found',
                    405 => 'method_not_allowed',
                    409 => 'conflict',
                    default => 'http_error',
                },
                $message,
                $status,
            );
        });
    })->create();
