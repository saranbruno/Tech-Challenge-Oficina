<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('initial_admin.password');

        if (! is_string($password) || $password === '') {
            throw new RuntimeException('ADMIN_PASSWORD deve ser configurada para criar o administrador inicial.');
        }

        User::query()->updateOrCreate(
            ['email' => config('initial_admin.email')],
            [
                'name' => config('initial_admin.name'),
                'password' => $password,
            ],
        );
    }
}
