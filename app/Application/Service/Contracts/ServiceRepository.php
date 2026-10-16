<?php

namespace App\Application\Service\Contracts;

use App\Application\Shared\Data\PaginatedResult;
use App\Domain\Service\Service;

interface ServiceRepository
{
    public function paginate(int $perPage): PaginatedResult;

    public function findOrFail(int $id): Service;

    public function save(Service $service): Service;

    public function delete(int $id): void;
}
