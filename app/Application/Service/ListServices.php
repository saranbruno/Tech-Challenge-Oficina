<?php

namespace App\Application\Service;

use App\Application\Service\Contracts\ServiceRepository;
use App\Application\Shared\Data\PaginatedResult;

final readonly class ListServices
{
    public function __construct(private ServiceRepository $services) {}

    public function execute(int $perPage): PaginatedResult
    {
        return $this->services->paginate($perPage);
    }
}
