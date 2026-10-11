<?php

namespace App\Application\Service;

use App\Application\Service\Contracts\ServiceRepository;

final readonly class DeleteService
{
    public function __construct(private ServiceRepository $services) {}

    public function execute(int $id): void
    {
        $this->services->delete($id);
    }
}
