<?php

namespace App\Application\Service;

use App\Application\Service\Contracts\ServiceRepository;

final readonly class ListServices
{
    public function __construct(private ServiceRepository $services) {}

    public function execute(int $perPage): mixed
    {
        return $this->services->paginate($perPage);
    }
}
