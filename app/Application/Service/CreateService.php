<?php

namespace App\Application\Service;

use App\Application\Service\Contracts\ServiceRepository;
use App\Domain\Service\Service;

final readonly class CreateService
{
    public function __construct(
        private ServiceRepository $services,
        private ServiceDataFactory $factory,
    ) {}

    public function execute(string $name, int $unitPrice): Service
    {
        return $this->services->save($this->factory->make(null, $name, $unitPrice));
    }
}
