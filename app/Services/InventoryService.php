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
                    'cost_price' => $costPrice,
                    'last_updated' => now(),
                ]);
            }

            // Atualiza custo do produto
            $product->cost_price = $costPrice;
            $product->save();

            Cache::forget('inventory:summary');
        });
    }

    public function getInventorySummary()
    {
        // TODO: Implementar paginação se necessário
        // TODO: Criar um config para o tempo de cache
        return Cache::remember('inventory:summary', now()->addMinutes(60), function () {
            return DB::table('inventories')
                ->join('products', 'inventories.product_id', '=', 'products.id')
                ->whereNull('inventories.deleted_at')
                ->whereNull('products.deleted_at')
                ->select(
                    'products.sku',
                    'products.name',
                    'products.description',
                    'inventories.quantity',
                    'products.cost_price',
                    'products.sale_price',
                    DB::raw('(inventories.quantity * products.cost_price) as total_cost'),
                    DB::raw('(inventories.quantity * (products.sale_price - products.cost_price)) as projected_profit'),
                    'inventories.last_updated'
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
