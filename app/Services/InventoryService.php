<?php

namespace App\Services;

use App\Repositories\Contracts\InventoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(
        private InventoryRepositoryInterface $inventoryRepository,
        private ProductRepositoryInterface $productRepository
    ) {}

    public function addStock(string $sku, int $quantity, float $costPrice): void
    {
        DB::transaction(function () use ($sku, $quantity, $costPrice) {
            $product = $this->productRepository->findBySku($sku);

            if (! $product) {
                throw new \Exception("Produto não encontrado: {$sku}");
            }

            $inventory = $this->inventoryRepository->findByProductId($product->id);

            if ($inventory) {
                $inventory->quantity += $quantity;
                $inventory->last_updated = now();
                $inventory->save();
            } else {
                $this->inventoryRepository->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'last_updated' => now(),
                ]);
            }

            // Atualiza custo do produto
            $product->cost_price = $costPrice;
            $product->save();

            // Invalida cache do estoque
            Cache::forget('inventory:summary');
        });
    }

    public function getInventorySummary()
    {
        return Cache::remember('inventory:summary', 60, function () {
            return DB::table('inventory')
                ->join('products', 'inventory.product_id', '=', 'products.id')
                ->select(
                    'products.sku',
                    'products.name',
                    'inventory.quantity',
                    'products.cost_price',
                    'products.sale_price',
                    DB::raw('(inventory.quantity * products.cost_price) as total_cost'),
                    DB::raw('(inventory.quantity * (products.sale_price - products.cost_price)) as projected_profit')
                )
                ->get();
        });
    }

    public function cleanOldInventory(): int
    {
        $deleted = $this->inventoryRepository->deleteOlderThan(now()->subDays(90));

        if ($deleted > 0) {
            Cache::forget('inventory:summary');
        }

        return $deleted;
    }
}
