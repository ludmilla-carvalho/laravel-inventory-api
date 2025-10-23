<?php

namespace Tests\Feature;

use App\Enums\SaleStatus;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
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
                    'name' => 'Laptop',
                    'sale_price' => 1500.00,
                    'cost_price' => 1000.00,
                    'description' => 'High-end laptop',
                ],
                'sales' => [
                    ['quantity' => 2, 'status' => SaleStatus::Completed],
                    ['quantity' => 1, 'status' => SaleStatus::Completed],
                ],
            ],
            [
                'product' => [
                    'name' => 'Mouse',
                    'sale_price' => 50.00,
                    'cost_price' => 20.00,
                    'description' => 'Wireless mouse',
                ],
                'sales' => [
                    ['quantity' => 5, 'status' => SaleStatus::Completed],
                    ['quantity' => 3, 'status' => SaleStatus::Processing],
                ],
            ],
            [
                'product' => [
                    'name' => 'Keyboard',
                    'sale_price' => 120.00,
                    'cost_price' => 60.00,
                    'description' => 'Mechanical keyboard',
                ],
                'sales' => [
                    ['quantity' => 4, 'status' => SaleStatus::Completed],
                ],
            ],
        ];

        // Create products and sales
        $this->createProductsAndSales();
    }

    private function createProductsAndSales(): void
    {
        foreach ($this->productsData as $data) {
            $product = Product::factory()->create($data['product']);
            $this->products[] = $product;

            // Initialize inventory
            $this->postJson('/api/inventory', [
                'sku' => $product->sku,
                'quantity' => 100,
                'cost_price' => $data['product']['cost_price'],
            ]);

            // Create sales for this product
            foreach ($data['sales'] as $saleData) {
                $sale = Sale::factory()->create([
                    'total_amount' => $data['product']['sale_price'] * $saleData['quantity'],
                    'total_cost' => $data['product']['cost_price'] * $saleData['quantity'],
                    'total_profit' => ($data['product']['sale_price'] - $data['product']['cost_price']) * $saleData['quantity'],
                    'status' => $saleData['status'],
                ]);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $saleData['quantity'],
                    'unit_price' => $data['product']['sale_price'],
                    'unit_cost' => $data['product']['cost_price'],
                ]);
            }
        }
    }

    public function test_can_get_sales_report_without_filters(): void
    {
        $response = $this->getJson('/api/reports/sales');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'sku',
                        'product_name',
                        'total_quantity',
                        'total_sales',
                        'total_cost',
                        'total_profit',
                    ],
                ],
                'message',
            ]);

        // Verify we have data for 3 products (only completed sales)
        $data = $response->json('data');
        $this->assertCount(3, $data);

        // Verify Laptop calculations (2 + 1 = 3 units, both completed)
        $laptop = collect($data)->firstWhere('product_name', 'Laptop');
        $this->assertNotNull($laptop);
        $this->assertEquals(3, $laptop['total_quantity']);
        $this->assertEquals(4500.00, $laptop['total_sales']); // 1500 * 3
        $this->assertEquals(3000.00, $laptop['total_cost']); // 1000 * 3
        $this->assertEquals(1500.00, $laptop['total_profit']); // 500 * 3

        // Verify Mouse calculations (only 5 units from completed sale)
        $mouse = collect($data)->firstWhere('product_name', 'Mouse');
        $this->assertNotNull($mouse);
        $this->assertEquals(5, $mouse['total_quantity']); // Only completed sales
        $this->assertEquals(250.00, $mouse['total_sales']); // 50 * 5
        $this->assertEquals(100.00, $mouse['total_cost']); // 20 * 5
        $this->assertEquals(150.00, $mouse['total_profit']); // 30 * 5

        // Verify Keyboard calculations (4 units)
        $keyboard = collect($data)->firstWhere('product_name', 'Keyboard');
        $this->assertNotNull($keyboard);
        $this->assertEquals(4, $keyboard['total_quantity']);
        $this->assertEquals(480.00, $keyboard['total_sales']); // 120 * 4
        $this->assertEquals(240.00, $keyboard['total_cost']); // 60 * 4
        $this->assertEquals(240.00, $keyboard['total_profit']); // 60 * 4
    }

    public function test_can_filter_sales_report_by_sku(): void
    {
        $laptopSku = $this->products[0]->sku;

        $response = $this->getJson("/api/reports/sales?sku={$laptopSku}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'sku',
                        'product_name',
                        'total_quantity',
                        'total_sales',
                        'total_cost',
                        'total_profit',
                    ],
                ],
            ]);

        // Verify we only have data for the laptop
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($laptopSku, $data[0]['sku']);
        $this->assertEquals('Laptop', $data[0]['product_name']);
        $this->assertEquals(3, $data[0]['total_quantity']);
    }

    public function test_can_filter_sales_report_by_date_range(): void
    {
        $startDate = now()->subDays(1)->toDateString();
        $endDate = now()->addDays(1)->toDateString();

        $response = $this->getJson("/api/reports/sales?start_date={$startDate}&end_date={$endDate}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'sku',
                        'product_name',
                        'total_quantity',
                        'total_sales',
                        'total_cost',
                        'total_profit',
                    ],
                ],
            ]);

        // All sales should be included since they were created today
        $data = $response->json('data');
        $this->assertCount(3, $data);
    }

    public function test_returns_empty_report_for_date_range_with_no_sales(): void
    {
        $startDate = now()->subYears(2)->toDateString();
        $endDate = now()->subYears(1)->toDateString();

        $response = $this->getJson("/api/reports/sales?start_date={$startDate}&end_date={$endDate}");

        $response
            ->assertStatus(200)
            ->assertJsonPath('data', []);
    }

    public function test_can_filter_sales_report_by_sku_and_date_range(): void
    {
        $laptopSku = $this->products[0]->sku;
        $startDate = now()->subDays(1)->toDateString();
        $endDate = now()->addDays(1)->toDateString();

        $response = $this->getJson("/api/reports/sales?sku={$laptopSku}&start_date={$startDate}&end_date={$endDate}");

        $response
            ->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($laptopSku, $data[0]['sku']);
    }

    public function test_returns_empty_report_for_invalid_sku(): void
    {
        $response = $this->getJson('/api/reports/sales?sku=invalid-sku-9999');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    public function test_returns_validation_error_for_invalid_date_format(): void
    {
        $response = $this->getJson('/api/reports/sales?start_date=invalid-date&end_date=2024-01-01');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);
    }

    public function test_sales_report_excludes_processing_sales(): void
    {
        // Mouse has 5 completed and 3 processing sales
        $mouseSku = $this->products[1]->sku;

        $response = $this->getJson("/api/reports/sales?sku={$mouseSku}");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);

        // Should only count the 5 completed sales, not the 3 processing ones
        $this->assertEquals(5, $data[0]['total_quantity']);
        $this->assertEquals(250.00, $data[0]['total_sales']); // 50 * 5
    }

    public function test_sales_report_returns_correct_message(): void
    {
        $response = $this->getJson('/api/reports/sales');

        $response
            ->assertStatus(200)
            ->assertJsonPath('message', 'Relatório de vendas gerado');
    }

    public function test_sales_report_with_multiple_sales_aggregates_correctly(): void
    {
        // Create an additional completed sale for laptop
        $laptop = $this->products[0];

        $sale = Sale::factory()->create([
            'total_amount' => 3000.00, // 1500 * 2
            'total_cost' => 2000.00, // 1000 * 2
            'total_profit' => 1000.00, // 500 * 2
            'status' => SaleStatus::Completed,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $laptop->id,
            'quantity' => 2,
            'unit_price' => 1500.00,
            'unit_cost' => 1000.00,
        ]);

        $response = $this->getJson("/api/reports/sales?sku={$laptop->sku}");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);

        // Should now have 5 total units (3 from setup + 2 new)
        $this->assertEquals(5, $data[0]['total_quantity']);
        $this->assertEquals(7500.00, $data[0]['total_sales']); // 1500 * 5
        $this->assertEquals(5000.00, $data[0]['total_cost']); // 1000 * 5
        $this->assertEquals(2500.00, $data[0]['total_profit']); // 500 * 5
    }

    public function test_sales_report_returns_empty_when_no_completed_sales(): void
    {
        // Create a new product with only processing sales
        $product = Product::factory()->create([
            'name' => 'Monitor',
            'sale_price' => 500.00,
            'cost_price' => 300.00,
        ]);

        $this->postJson('/api/inventory', [
            'sku' => $product->sku,
            'quantity' => 50,
            'cost_price' => 300.00,
        ]);

        $sale = Sale::factory()->create([
            'total_amount' => 1000.00,
            'total_cost' => 600.00,
            'total_profit' => 400.00,
            'status' => SaleStatus::Processing,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 500.00,
            'unit_cost' => 300.00,
        ]);

        $response = $this->getJson("/api/reports/sales?sku={$product->sku}");

        $response
            ->assertStatus(200)
            ->assertJsonPath('data', []);
    }
}
