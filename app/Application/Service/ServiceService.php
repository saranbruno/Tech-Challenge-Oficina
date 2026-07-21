<?php

namespace App\Application\Service;

use App\Application\Service\Contracts\ServiceRepository;
use App\Domain\Service\Service;
use App\Domain\Service\ValueObjects\UnitPrice;

final readonly class ServiceService
{
    public function __construct(private ServiceRepository $services) {}

    public function list(int $perPage): mixed
    {
        return $this->services->paginate($perPage);
    }

    public function find(int $id): Service
    {
        return $this->services->findOrFail($id);
    }

    public function create(string $name, int $unitPrice): Service
    {
        return $this->services->save(new Service(null, $name, new UnitPrice($unitPrice)));
    }

    public function update(int $id, string $name, int $unitPrice): Service
    {
        $this->services->findOrFail($id);

        return $this->services->save(new Service($id, $name, new UnitPrice($unitPrice)));
    }

    public function delete(int $id): void
    {
        $this->services->delete($id);
    }
}
