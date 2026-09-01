<?php

namespace Tests\Feature\Api;

use App\Infrastructure\Persistence\Eloquent\Models\CustomerModel;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_routes_require_authentication(): void
    {
        $this->getJson('/api/admin/customers')->assertUnauthorized();
        $this->postJson('/api/admin/customers', [])->assertUnauthorized();
    }

    public function test_admin_can_create_list_show_update_and_delete_a_customer(): void
    {
        $token = $this->adminToken();
        $created = $this->withToken($token)->postJson('/api/admin/customers', [
            'name' => 'Maria da Silva',
            'document' => '529.982.247-25',
        ])->assertCreated()
            ->assertJsonPath('data.name', 'Maria da Silva')
            ->assertJsonPath('data.document', '52998224725')
            ->assertJsonPath('data.document_type', 'cpf');

        $customerId = $created->json('data.id');

        $this->withToken($token)->getJson('/api/admin/customers')
            ->assertOk()
            ->assertJsonPath('data.0.id', $customerId)
            ->assertJsonStructure(['data', 'links', 'meta']);

        $this->withToken($token)->getJson("/api/admin/customers/{$customerId}")
            ->assertOk()
            ->assertJsonPath('data.document', '52998224725');

        $this->withToken($token)->putJson("/api/admin/customers/{$customerId}", [
            'name' => 'Oficina Cliente Ltda',
            'document' => '04.252.011/0001-10',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Oficina Cliente Ltda')
            ->assertJsonPath('data.document', '04252011000110')
            ->assertJsonPath('data.document_type', 'cnpj');

        $this->withToken($token)->deleteJson("/api/admin/customers/{$customerId}")
            ->assertNoContent();

        $this->assertDatabaseMissing('customers', ['id' => $customerId]);
    }

    public function test_invalid_document_is_rejected(): void
    {
        $this->withToken($this->adminToken())->postJson('/api/admin/customers', [
            'name' => 'Cliente Invalido',
            'document' => '111.111.111-11',
        ])->assertUnprocessable()
            ->assertJsonPath('error.code', 'invalid_document');
    }

    public function test_required_fields_are_validated(): void
    {
        $this->withToken($this->adminToken())->postJson('/api/admin/customers', [])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_error')
            ->assertJsonStructure(['error' => ['details' => ['name', 'document']]]);
    }

    public function test_duplicate_document_is_blocked_by_the_application(): void
    {
        $payload = ['name' => 'Primeiro Cliente', 'document' => '52998224725'];

        $token = $this->adminToken();
        $this->withToken($token)->postJson('/api/admin/customers', $payload)->assertCreated();

        $this->withToken($token)->postJson('/api/admin/customers', [
            'name' => 'Segundo Cliente',
            'document' => '529.982.247-25',
        ])->assertConflict()
            ->assertJsonPath('error.code', 'duplicate_document');
    }

    public function test_duplicate_document_is_blocked_by_postgresql(): void
    {
        CustomerModel::query()->create([
            'name' => 'Primeiro Cliente',
            'document' => '52998224725',
            'document_type' => 'cpf',
        ]);

        $this->expectException(QueryException::class);

        CustomerModel::query()->create([
            'name' => 'Segundo Cliente',
            'document' => '52998224725',
            'document_type' => 'cpf',
        ]);
    }

    public function test_missing_customer_returns_not_found(): void
    {
        $this->withToken($this->adminToken())->getJson('/api/admin/customers/999999')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'not_found');
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
