<?php

namespace Tests\Architecture\Fixtures;

use App\Infrastructure\Persistence\Eloquent\Models\CustomerModel;

final class ForbiddenApplicationDependency
{
    public function execute(): mixed
    {
        return config(CustomerModel::class);
    }
}
