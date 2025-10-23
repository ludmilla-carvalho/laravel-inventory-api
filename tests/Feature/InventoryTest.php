<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_inventory_summary(): void
    {
        // Create a product with inventory
        $product = Product::factory()->create();

        // Add inventory through the API
        $this->postJson('/api/inventory', [
            'sku' => $product->sku,
            'quantity' => 10,
            'cost_price' => 100.00,
        ]);

        // Get inventory summary
        $response = $this->getJson('/api/inventory');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'sku',
                        'name',
                        'description',
                        'quantity',
                        'cost_price',
                        'sale_price',
                        'total_cost',
                        'projected_profit',
                    ],
                ],
                'message',
            ]);
    }

    public function test_can_add_inventory(): void
    {
        // Create a product
        $product = Product::factory()->create();

        $response = $this->postJson('/api/inventory', [
            'sku' => $product->sku,
            'quantity' => 10,
            'cost_price' => 100.00,
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'message' => 'Estoque atualizado com sucesso',
            ]);

        // Verify the inventory was created in database
        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'cost_price' => 100.00,
        ]);
    }

    public function test_cannot_add_inventory_with_invalid_sku(): void
    {
        $response = $this->postJson('/api/inventory', [
            'sku' => 'invalid-sku',
            'quantity' => 10,
            'cost_price' => 100.00,
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_add_inventory_with_invalid_quantity(): void
    {
        // Create a product
        $product = Product::factory()->create();

        $response = $this->postJson('/api/inventory', [
            'sku' => $product->sku,
            'quantity' => 0, // Invalid quantity
            'cost_price' => 100.00,
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_add_inventory_with_invalid_cost_price(): void
    {
        // Create a product
        $product = Product::factory()->create();

        $response = $this->postJson('/api/inventory', [
            'sku' => $product->sku,
            'quantity' => 10,
            'cost_price' => -1.00, // Invalid cost price
        ]);

        $response->assertStatus(422);
    }

    public function test_can_update_existing_inventory(): void
    {
        // Create a product
        $product = Product::factory()->create();

        // Add initial inventory
        $this->postJson('/api/inventory', [
            'sku' => $product->sku,
            'quantity' => 10,
            'cost_price' => 100.00,
        ]);

        // Add more inventory
        $response = $this->postJson('/api/inventory', [
            'sku' => $product->sku,
            'quantity' => 5,
            'cost_price' => 110.00,
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'message' => 'Estoque atualizado com sucesso',
            ]);

        // Verify the inventory was updated in database
        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'quantity' => 15, // Should be the sum of both additions
        ]);
    }

    public function test_inventory_summary_shows_correct_totals(): void
    {
        // Create a product
        $product = Product::factory()->create([
            'sale_price' => 150.00,
        ]);

        // Add inventory
        $this->postJson('/api/inventory', [
            'sku' => $product->sku,
            'quantity' => 10,
            'cost_price' => 100.00,
        ]);

        $response = $this->getJson('/api/inventory');

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'sku' => $product->sku,
                        'quantity' => 10,
                        'cost_price' => 100.00,
                        'sale_price' => 150.00,
                        'total_cost' => 1000.00, // quantity * cost_price
                        'projected_profit' => 500.00, // quantity * (sale_price - cost_price)
                    ],
                ],
            ]);
    }

    public function test_cannot_add_inventory_with_missing_fields(): void
    {
        $product = Product::factory()->create();

        // Test missing quantity
        $response1 = $this->postJson('/api/inventory', [
            'sku' => $product->sku,
            'cost_price' => 100.00,
        ]);
        $response1->assertStatus(422);

        // Test missing cost_price
        $response2 = $this->postJson('/api/inventory', [
            'sku' => $product->sku,
            'quantity' => 10,
        ]);
        $response2->assertStatus(422);

        // Test missing sku
        $response3 = $this->postJson('/api/inventory', [
            'quantity' => 10,
            'cost_price' => 100.00,
        ]);
        $response3->assertStatus(422);
    }

    public function test_inventory_is_soft_deleted(): void
    {
        // Create a product
        $product = Product::factory()->create();

        // Add inventory
        $this->postJson('/api/inventory', [
            'sku' => $product->sku,
            'quantity' => 10,
            'cost_price' => 100.00,
        ]);

        // Soft delete the product
        $product->delete();

        // The product should not appear in the inventory summary
        $response = $this->getJson('/api/inventory');
        $response
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');

        // But it should still exist in the database
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'deleted_at' => $product->deleted_at,
        ]);
    }
}
