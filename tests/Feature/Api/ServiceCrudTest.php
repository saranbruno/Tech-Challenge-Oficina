<?php

namespace Tests\Feature\Api;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ServiceCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_routes_require_authentication(): void
    {
        $this->getJson('/api/admin/services')->assertUnauthorized();
        $this->postJson('/api/admin/services', [])->assertUnauthorized();
    }

    public function test_admin_can_create_list_show_update_and_delete_a_service(): void
    {
        $token = $this->adminToken();

        $created = $this->withToken($token)->postJson('/api/admin/services', [
            'name' => 'Troca de oleo',
            'unit_price' => 15990,
        ])->assertCreated()
            ->assertJsonPath('data.name', 'Troca de oleo')
            ->assertJsonPath('data.unit_price', 15990);

        $serviceId = $created->json('data.id');

        $this->withToken($token)->getJson('/api/admin/services')
            ->assertOk()
            ->assertJsonPath('data.0.id', $serviceId)
            ->assertJsonStructure(['data', 'links', 'meta']);

        $this->withToken($token)->getJson("/api/admin/services/{$serviceId}")
            ->assertOk()
            ->assertJsonPath('data.unit_price', 15990);

        $this->withToken($token)->putJson("/api/admin/services/{$serviceId}", [
            'name' => 'Alinhamento',
            'unit_price' => 8990,
        ])->assertOk()
            ->assertJsonPath('data.name', 'Alinhamento')
            ->assertJsonPath('data.unit_price', 8990);

        $this->withToken($token)->deleteJson("/api/admin/services/{$serviceId}")->assertNoContent();
        $this->assertDatabaseMissing('services', ['id' => $serviceId]);
    }

    public function test_required_fields_and_invalid_prices_are_rejected(): void
    {
        $token = $this->adminToken();

        $this->withToken($token)->postJson('/api/admin/services', [])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_error');

        $this->withToken($token)->postJson('/api/admin/services', [
            'name' => 'Alinhamento',
            'unit_price' => -1,
        ])->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_error');

        $this->withToken($token)->postJson('/api/admin/services', [
            'name' => 'Alinhamento',
            'unit_price' => 89.90,
        ])->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_error');
    }

    public function test_service_names_are_trimmed_and_zero_price_is_supported(): void
    {
        $this->withToken($this->adminToken())->postJson('/api/admin/services', [
            'name' => '  Inspecao  ',
            'unit_price' => 0,
        ])->assertCreated()
            ->assertJsonPath('data.name', 'Inspecao')
            ->assertJsonPath('data.unit_price', 0);
    }

    public function test_postgresql_prevents_negative_unit_prices(): void
    {
        $this->expectException(QueryException::class);

        DB::table('services')->insert([
            'name' => 'Servico invalido',
            'unit_price' => -1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_missing_service_returns_not_found(): void
    {
        $token = $this->adminToken();

        $this->withToken($token)->getJson('/api/admin/services/999999')->assertNotFound();
        $this->withToken($token)->putJson('/api/admin/services/999999', [
            'name' => 'Alinhamento',
            'unit_price' => 8990,
        ])->assertNotFound();
        $this->withToken($token)->deleteJson('/api/admin/services/999999')->assertNotFound();
    }

    private function adminToken(): string
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'secure-password',
        ]);

        return $this->postJson('/api/admin/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'secure-password',
        ])->json('access_token');
    }
}
