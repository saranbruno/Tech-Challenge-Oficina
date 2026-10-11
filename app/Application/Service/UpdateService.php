<?php

namespace App\Application\Service;

use App\Application\Service\Contracts\ServiceRepository;
use App\Domain\Service\Service;

final readonly class UpdateService
{
    public function __construct(
        private ServiceRepository $services,
        private ServiceDataFactory $factory,
    ) {}

    public function execute(int $id, string $name, int $unitPrice): Service
    {
        $this->services->findOrFail($id);

        return $this->services->save($this->factory->make($id, $name, $unitPrice));
    }
}
