<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Application\Shared\Data\PaginatedResult;
use Closure;
use Illuminate\Pagination\LengthAwarePaginator;

final class PaginatedResultFactory
{
    public static function make(LengthAwarePaginator $paginator, Closure $mapper): PaginatedResult
    {
        return new PaginatedResult(
            array_map($mapper, $paginator->items()),
            $paginator->currentPage(),
            $paginator->perPage(),
            $paginator->total(),
            $paginator->lastPage(),
        );
    }
}
