<?php

namespace App\Interfaces\Http\Pagination;

use App\Application\Shared\Data\PaginatedResult;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

final class LengthAwarePaginatorFactory
{
    public static function make(PaginatedResult $result, Request $request): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            $result->items,
            $result->total,
            $result->perPage,
            $result->currentPage,
            ['path' => $request->url()],
        );
    }
}
