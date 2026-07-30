<?php

use App\Application\Auth\Exceptions\InvalidCredentials;
use App\Application\Auth\Exceptions\InvalidRefreshToken;
use App\Application\Customer\Exceptions\DuplicateCustomerDocument;
use App\Application\Inventory\Exceptions\InventoryItemHasMovements;
use App\Application\ServiceOrder\Exceptions\InsufficientInventoryStock;
use App\Application\ServiceOrder\Exceptions\VehicleDoesNotBelongToCustomer;
use App\Application\Vehicle\Exceptions\DuplicateLicensePlate;
use App\Domain\Customer\Exceptions\InvalidDocument;
use App\Domain\ServiceOrder\Exceptions\InvalidAdditionalRepair;
use App\Domain\ServiceOrder\Exceptions\InvalidServiceOrderBudget;
use App\Domain\ServiceOrder\Exceptions\InvalidServiceOrderTransition;
use App\Domain\Vehicle\Exceptions\InvalidLicensePlate;
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

        $exceptions->render(function (InvalidDocument $exception, Request $request) {
            return ApiErrorResponse::make(
                'invalid_document',
                $exception->getMessage(),
                422,
            );
        });

        $exceptions->render(function (DuplicateCustomerDocument $exception, Request $request) {
            return ApiErrorResponse::make(
                'duplicate_document',
                $exception->getMessage(),
                409,
            );
        });

        $exceptions->render(function (InventoryItemHasMovements $exception, Request $request) {
            return ApiErrorResponse::make('inventory_item_has_movements', $exception->getMessage(), 409);
        });

        $exceptions->render(function (VehicleDoesNotBelongToCustomer $exception, Request $request) {
            return ApiErrorResponse::make('vehicle_not_owned_by_customer', $exception->getMessage(), 422);
        });

        $exceptions->render(function (InvalidServiceOrderTransition $exception, Request $request) {
            return ApiErrorResponse::make('invalid_service_order_transition', $exception->getMessage(), 409);
        });

        $exceptions->render(function (InsufficientInventoryStock $exception, Request $request) {
            return ApiErrorResponse::make('insufficient_inventory_stock', $exception->getMessage(), 409);
        });

        $exceptions->render(function (InvalidServiceOrderBudget $exception, Request $request) {
            return ApiErrorResponse::make('invalid_service_order_budget', $exception->getMessage(), 409);
        });

        $exceptions->render(function (InvalidAdditionalRepair $exception, Request $request) {
            return ApiErrorResponse::make('invalid_additional_repair', $exception->getMessage(), 409);
        });

        $exceptions->render(function (InvalidLicensePlate $exception, Request $request) {
            return ApiErrorResponse::make('invalid_license_plate', $exception->getMessage(), 422);
        });

        $exceptions->render(function (DuplicateLicensePlate $exception, Request $request) {
            return ApiErrorResponse::make('duplicate_license_plate', $exception->getMessage(), 409);
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
