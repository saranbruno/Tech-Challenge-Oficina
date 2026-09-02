<?php

namespace Tests\Feature\Api;

use App\Infrastructure\Persistence\Eloquent\Models\CustomerModel;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
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
            ->assertJsonPath('data.document_type', 'cpf')
            ->assertJsonPath('data.email', null)
            ->assertJsonPath('data.phone', null);

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
            ->assertJsonPath('data.document_type', 'cnpj')
            ->assertJsonPath('data.email', null)
            ->assertJsonPath('data.phone', null);

        $this->withToken($token)->deleteJson("/api/admin/customers/{$customerId}")
            ->assertNoContent();

        $this->assertDatabaseMissing('customers', ['id' => $customerId]);
    }

    #[DataProvider('contactCombinationProvider')]
    public function test_customer_accepts_every_optional_contact_combination(
        ?string $email,
        ?string $phone,
        ?string $expectedEmail,
        ?string $expectedPhone,
    ): void {
        $response = $this->withToken($this->adminToken())->postJson('/api/admin/customers', [
            'name' => 'Cliente com contatos',
            'document' => '529.982.247-25',
            'email' => $email,
            'phone' => $phone,
        ])->assertCreated()
            ->assertJsonPath('data.email', $expectedEmail)
            ->assertJsonPath('data.phone', $expectedPhone);

        $this->assertDatabaseHas('customers', [
            'id' => $response->json('data.id'),
            'email' => $expectedEmail,
            'phone' => $expectedPhone,
        ]);
    }

    public static function contactCombinationProvider(): array
    {
        return [
            'somente email' => ['  Cliente@Example.COM  ', null, 'cliente@example.com', null],
            'somente telefone' => [null, '(11) 99876-5432', null, '+5511998765432'],
            'ambos' => ['cliente@example.com', '+1 (202) 555-0123', 'cliente@example.com', '+12025550123'],
        ];
    }

    public function test_customer_contacts_can_be_updated_and_cleared(): void
    {
        $token = $this->adminToken();
        $customerId = $this->withToken($token)->postJson('/api/admin/customers', [
            'name' => 'Cliente',
            'document' => '52998224725',
        ])->json('data.id');

        $this->withToken($token)->putJson("/api/admin/customers/{$customerId}", [
            'name' => 'Cliente',
            'document' => '52998224725',
            'email' => 'ATENDIMENTO@EXAMPLE.COM',
            'phone' => '55 11 99876-5432',
        ])->assertOk()
            ->assertJsonPath('data.email', 'atendimento@example.com')
            ->assertJsonPath('data.phone', '+5511998765432');

        $this->withToken($token)->putJson("/api/admin/customers/{$customerId}", [
            'name' => 'Cliente',
            'document' => '52998224725',
            'email' => null,
            'phone' => null,
        ])->assertOk()
            ->assertJsonPath('data.email', null)
            ->assertJsonPath('data.phone', null);

        $this->assertDatabaseHas('customers', [
            'id' => $customerId,
            'email' => null,
            'phone' => null,
        ]);
    }

    public function test_malformed_contacts_are_rejected(): void
    {
        $token = $this->adminToken();

        $this->withToken($token)->postJson('/api/admin/customers', [
            'name' => 'Cliente',
            'document' => '52998224725',
            'email' => 'email-invalido',
        ])->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_error');

        $this->withToken($token)->postJson('/api/admin/customers', [
            'name' => 'Cliente',
            'document' => '52998224725',
            'phone' => '12345',
        ])->assertUnprocessable()
            ->assertJsonPath('error.code', 'invalid_phone');
    }

    public function test_contacts_do_not_replace_document_as_customer_identity(): void
    {
        $token = $this->adminToken();
        $contacts = [
            'email' => 'shared@example.com',
            'phone' => '+5511998765432',
        ];

        $this->withToken($token)->postJson('/api/admin/customers', [
            'name' => 'Primeiro Cliente',
            'document' => '52998224725',
            ...$contacts,
        ])->assertCreated();

        $this->withToken($token)->postJson('/api/admin/customers', [
            'name' => 'Segundo Cliente',
            'document' => '04.252.011/0001-10',
            ...$contacts,
        ])->assertCreated();
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

    public function test_postgresql_rejects_phone_outside_e164(): void
    {
        $this->expectException(QueryException::class);

        CustomerModel::query()->create([
            'name' => 'Cliente',
            'document' => '52998224725',
            'document_type' => 'cpf',
            'phone' => '11998765432',
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
