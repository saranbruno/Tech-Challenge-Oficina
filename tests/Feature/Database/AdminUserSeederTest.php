<?php

namespace Tests\Feature\Database;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class AdminUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_the_generic_admin_from_environment_configuration(): void
    {
        config()->set('initial_admin', [
            'name' => 'Administrator',
            'email' => 'dev@email.com',
            'password' => 'environment-password',
        ]);

        $this->seed(AdminUserSeeder::class);

        $this->assertDatabaseHas('users', [
            'name' => 'Administrator',
            'email' => 'dev@email.com',
        ]);

        $this->assertTrue(Hash::check(
            'environment-password',
            User::query()->where('email', 'dev@email.com')->value('password'),
        ));
    }

    public function test_seeder_rejects_a_missing_password(): void
    {
        config()->set('initial_admin.password');

        $this->expectException(RuntimeException::class);

        $this->seed(AdminUserSeeder::class);
    }
}
