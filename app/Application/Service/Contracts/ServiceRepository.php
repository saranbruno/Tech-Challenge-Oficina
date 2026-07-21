<?php

namespace App\Application\Service\Contracts;

use App\Domain\Service\Service;

interface ServiceRepository
{
    public function paginate(int $perPage): mixed;

    public function findOrFail(int $id): Service;

    public function save(Service $service): Service;

    public function delete(int $id): void;
}
