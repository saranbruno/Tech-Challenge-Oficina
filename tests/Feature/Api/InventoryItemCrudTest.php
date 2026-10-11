<?php

namespace Tests\Feature\Api;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InventoryItemCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_routes_require_authentication(): void
    {
        $this->getJson('/api/admin/inventory-items')->assertUnauthorized();
        $this->postJson('/api/admin/inventory-items', [])->assertUnauthorized();
        $this->putJson('/api/admin/inventory-items/1/stock', [])->assertUnauthorized();
        $this->getJson('/api/admin/inventory-items/1/movements')->assertUnauthorized();
    }

    public function test_admin_can_manage_parts_and_supplies(): void
    {
        $token = $this->adminToken();

        $created = $this->withToken($token)->postJson('/api/admin/inventory-items', [
            'name' => '  Filtro de oleo  ',
            'type' => 'part',
            'unit_price' => 4590,
            'quantity_available' => 0,
        ])->assertCreated()
            ->assertJsonPath('data.name', 'Filtro de oleo')
            ->assertJsonPath('data.type', 'part')
            ->assertJsonPath('data.unit_price', 4590)
            ->assertJsonPath('data.quantity_available', 0);

        $itemId = $created->json('data.id');

        $this->withToken($token)->getJson('/api/admin/inventory-items')
            ->assertOk()
            ->assertJsonPath('data.0.id', $itemId)
            ->assertJsonStructure(['data', 'links', 'meta']);

        $this->withToken($token)->getJson("/api/admin/inventory-items/{$itemId}")
            ->assertOk()
            ->assertJsonPath('data.type', 'part');

        $this->withToken($token)->putJson("/api/admin/inventory-items/{$itemId}", [
            'name' => 'Oleo 5W30',
            'type' => 'supply',
            'unit_price' => 5290,
        ])->assertOk()
            ->assertJsonPath('data.name', 'Oleo 5W30')
            ->assertJsonPath('data.type', 'supply')
            ->assertJsonPath('data.quantity_available', 0);

        $this->withToken($token)->deleteJson("/api/admin/inventory-items/{$itemId}")->assertNoContent();
        $this->assertDatabaseMissing('inventory_items', ['id' => $itemId]);
    }

    public function test_initial_stock_and_manual_adjustments_are_recorded(): void
    {
        $token = $this->adminToken();
        $adminId = User::query()->value('id');

        $created = $this->withToken($token)->postJson('/api/admin/inventory-items', [
            'name' => 'Pastilha de freio',
            'type' => 'part',
            'unit_price' => 12000,
            'quantity_available' => 10,
        ])->assertCreated();

        $itemId = $created->json('data.id');

        $this->assertDatabaseHas('stock_movements', [
            'inventory_item_id' => $itemId,
            'admin_user_id' => $adminId,
            'type' => 'initial_stock',
            'quantity_change' => 10,
            'quantity_before' => 0,
            'quantity_after' => 10,
        ]);

        $this->withToken($token)->putJson("/api/admin/inventory-items/{$itemId}/stock", [
            'quantity_available' => 4,
        ])->assertOk()
            ->assertJsonPath('data.quantity_available', 4);

        $this->assertDatabaseHas('stock_movements', [
            'inventory_item_id' => $itemId,
            'type' => 'manual_adjustment',
            'quantity_change' => -6,
            'quantity_before' => 10,
            'quantity_after' => 4,
        ]);

        $movements = $this->withToken($token)->getJson("/api/admin/inventory-items/{$itemId}/movements")
            ->assertOk()
            ->assertJsonPath('data.0.quantity_after', 4)
            ->assertJsonPath('data.1.quantity_after', 10)
            ->assertJsonStructure(['data', 'links', 'meta']);

        self::assertIsString($movements->json('data.0.created_at'));

        $this->withToken($token)->deleteJson("/api/admin/inventory-items/{$itemId}")
            ->assertConflict()
            ->assertJsonPath('error.code', 'inventory_item_has_movements');
    }

    public function test_invalid_catalogue_and_stock_values_are_rejected(): void
    {
        $token = $this->adminToken();

        $this->withToken($token)->postJson('/api/admin/inventory-items', [
            'name' => 'Item',
            'type' => 'tool',
            'unit_price' => -1,
            'quantity_available' => -1,
        ])->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_error');

        $itemId = $this->createItem($token);

        $this->withToken($token)->putJson("/api/admin/inventory-items/{$itemId}", [
            'name' => 'Item',
            'type' => 'part',
            'unit_price' => 100,
            'quantity_available' => 5,
        ])->assertUnprocessable();

        $this->withToken($token)->putJson("/api/admin/inventory-items/{$itemId}/stock", [
            'quantity_available' => -1,
        ])->assertUnprocessable();
    }

    public function test_postgresql_constraints_reject_invalid_inventory_data(): void
    {
        $this->expectException(QueryException::class);

        DB::table('inventory_items')->insert([
            'name' => 'Item invalido',
            'type' => 'tool',
            'unit_price' => 100,
            'quantity_available' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_postgresql_prevents_negative_stock(): void
    {
        $this->expectException(QueryException::class);

        DB::table('inventory_items')->insert([
            'name' => 'Item invalido',
            'type' => 'part',
            'unit_price' => 100,
            'quantity_available' => -1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_missing_inventory_item_returns_not_found(): void
    {
        $token = $this->adminToken();

        $this->withToken($token)->getJson('/api/admin/inventory-items/999999')->assertNotFound();
        $this->withToken($token)->putJson('/api/admin/inventory-items/999999/stock', [
            'quantity_available' => 1,
        ])->assertNotFound();
        $this->withToken($token)->getJson('/api/admin/inventory-items/999999/movements')->assertNotFound();
        $this->withToken($token)->deleteJson('/api/admin/inventory-items/999999')->assertNotFound();
    }

    private function createItem(string $token): int
    {
        return $this->withToken($token)->postJson('/api/admin/inventory-items', [
            'name' => 'Item',
            'type' => 'part',
            'unit_price' => 100,
            'quantity_available' => 0,
        ])->json('data.id');
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
