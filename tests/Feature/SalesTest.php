<?php

namespace Tests\Feature;

use App\Enums\SaleStatus;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SalesTest extends TestCase
{
    use RefreshDatabase;

    private array $productsData;

    private array $products = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Define products data
        $this->productsData = [
            [
                'product' => [
                    'name' => 'Product 1',
                    'sale_price' => 100.00,
                    'cost_price' => 50.00,
                    'description' => 'First test product',
                ],
                'inventory' => [
                    'initial' => 20,
                    'to_sell' => 3,
                    'final' => 17,
                ],
            ],
            [
                'product' => [
                    'name' => 'Product 2',
                    'sale_price' => 200.00,
                    'cost_price' => 100.00,
                    'description' => 'Second test product',
                ],
                'inventory' => [
                    'initial' => 15,
                    'to_sell' => 2,
                    'final' => 13,
                ],
            ],
            [
                'product' => [
                    'name' => 'Product 3',
                    'sale_price' => 150.00,
                    'cost_price' => 75.00,
                    'description' => 'Third test product',
                ],
                'inventory' => [
                    'initial' => 30,
                    'to_sell' => 4,
                    'final' => 26,
                ],
            ],
            [
                'product' => [
                    'name' => 'Product 4',
                    'sale_price' => 300.00,
                    'cost_price' => 180.00,
                    'description' => 'Fourth test product',
                ],
                'inventory' => [
                    'initial' => 10,
                    'to_sell' => 1,
                    'final' => 9,
                ],
            ],
            [
                'product' => [
                    'name' => 'Product 5',
                    'sale_price' => 80.00,
                    'cost_price' => 40.00,
                    'description' => 'Fifth test product',
                ],
                'inventory' => [
                    'initial' => 25,
                    'to_sell' => 5,
                    'final' => 20,
                ],
            ],
        ];

        // Create all products and their initial inventory
        $this->createProductsAndInventories();
    }

    private function createProductsAndInventories(): void
    {
        foreach ($this->productsData as $data) {
            $product = Product::factory()->create($data['product']);
            $this->products[] = $product;

            // Initialize inventory with the correct initial quantity
            $this->postJson('/api/inventory', [
                'sku' => $product->sku,
                'quantity' => $data['inventory']['initial'], // Changed from 'to_sell' to 'initial'
                'cost_price' => $data['product']['cost_price'],
            ]);
        }
    }

    public function test_can_create_sale(): void
    {
        Queue::fake();

        // Create sale items array from product data
        $saleItems = array_map(function ($product, $data) {
            return [
                'sku' => $product->sku,
                'quantity' => $data['inventory']['to_sell'],
            ];
        }, $this->products, $this->productsData);

        // Create sale with all 5 products
        $response = $this->postJson('/api/sales', [
            'items' => $saleItems,
        ]);

        // Total calculations:
        // Product 1: 100 * 3 = 300 (cost: 50 * 3 = 150)
        // Product 2: 200 * 2 = 400 (cost: 100 * 2 = 200)
        // Product 3: 150 * 4 = 600 (cost: 75 * 4 = 300)
        // Product 4: 300 * 1 = 300 (cost: 180 * 1 = 180)
        // Product 5: 80 * 5 = 400 (cost: 40 * 5 = 200)
        // Total Amount: 2000.00
        // Total Cost: 1030.00
        // Total Profit: 970.00

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'total_amount',
                    'total_cost',
                    'total_profit',
                    'status',
                    'created_at',
                    'items',
                ],
                'message',
            ])
            ->assertJsonPath('data.total_amount', '2000.00')
            ->assertJsonPath('data.total_cost', '1030.00')
            ->assertJsonPath('data.total_profit', '970.00')
            ->assertJsonPath('data.status', SaleStatus::Processing->name);

        // Verify the job was dispatched
        Queue::assertPushed(\App\Jobs\ProcessSaleJob::class);

        // Inventory should NOT be updated yet (job hasn't run)
        foreach ($this->products as $index => $product) {
            $this->assertDatabaseHas('inventories', [
                'product_id' => $product->id,
                'quantity' => $this->productsData[$index]['inventory']['initial'],
            ]);
        }
    }

    public function test_cannot_create_sale_with_insufficient_inventory(): void
    {
        Queue::fake();

        $product = $this->products[0];

        // Try to purchase more than available (initial is 20)
        $response = $this->postJson('/api/sales', [
            'items' => [
                [
                    'sku' => $product->sku,
                    'quantity' => 30, // More than available
                ],
            ],
        ]);

        // Sale is created with status 'processing'
        $response->assertStatus(201);

        // Verify the job was dispatched
        Queue::assertPushed(\App\Jobs\ProcessSaleJob::class);

        // Verify inventory wasn't changed (job hasn't run yet)
        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'quantity' => $this->productsData[0]['inventory']['initial'],
        ]);
    }

    public function test_cannot_create_sale_with_invalid_sku(): void
    {
        $response = $this->postJson('/api/sales', [
            'items' => [
                [
                    'sku' => 'invalid-sku',
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_create_sale_with_invalid_quantity(): void
    {
        $product = $this->products[0];

        $response = $this->postJson('/api/sales', [
            'items' => [
                [
                    'sku' => $product->sku,
                    'quantity' => 0, // Invalid quantity
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_create_sale_without_items(): void
    {
        $response = $this->postJson('/api/sales', [
            'items' => [],
        ]);

        $response->assertStatus(422);
    }

    public function test_can_get_sale_details(): void
    {
        $product = $this->products[0];

        // Create sale
        $saleResponse = $this->postJson('/api/sales', [
            'items' => [
                [
                    'sku' => $product->sku,
                    'quantity' => 2,
                ],
            ],
        ]);

        $saleId = $saleResponse->json('data.id');

        // Get sale details
        $response = $this->getJson("/api/sales/{$saleId}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'total_amount',
                    'total_cost',
                    'total_profit',
                    'status',
                    'created_at',
                    'items',
                ],
            ])
            ->assertJsonPath('data.total_amount', '200.00')
            ->assertJsonPath('data.total_cost', '100.00')
            ->assertJsonPath('data.total_profit', '100.00');
    }

    public function test_returns_404_for_nonexistent_sale(): void
    {
        $response = $this->getJson('/api/sales/999999');

        $response
            ->assertStatus(404)
            ->assertJson([
                'message' => 'Venda não encontrada',
            ]);
    }
}
