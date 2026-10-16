<?php

namespace App\Application\Shared\Data;

final readonly class PaginatedResult
{
    public function __construct(
        public array $items,
        public int $currentPage,
        public int $perPage,
        public int $total,
        public int $lastPage,
    ) {}
}
