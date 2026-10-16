<?php

namespace App\Application\Service;

use App\Application\Service\Contracts\ServiceRepository;
use App\Domain\Service\Service;

final readonly class GetService
{
    public function __construct(private ServiceRepository $services) {}

    public function execute(int $id): Service
    {
        return $this->services->findOrFail($id);
    }
}
