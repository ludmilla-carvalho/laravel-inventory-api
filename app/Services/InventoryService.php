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

    public function addStock(string $sku, int $quantity): void
    {
        DB::transaction(function () use ($sku, $quantity) {
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

            // Invalida cache do estoque (usar explicitamente redis)
            Cache::store('redis')->forget('inventory:summary');
        });
    }

    public function getInventorySummary()
    {
        // TODO: Implementar paginação se necessário
        // TODO: Criar um config para o tempo de cache
        // Cache for 60 minutes (usar explicitamente redis)
        return Cache::store('redis')->remember('inventory:summary', now()->addMinutes(60), function () {
            return DB::table('inventories')
                ->join('products', 'inventories.product_id', '=', 'products.id')
                ->select(
                    'products.sku',
                    'products.name',
                    'products.description',
                    'products.cost_price',
                    'products.sale_price',
                    'inventories.quantity',
                    'inventories.last_updated'
                )
                ->get();
        });
    }

    public function cleanOldInventory(): int
    {
        $deleted = $this->inventoryRepository->deleteOlderThan(now()->subDays(90));

        if ($deleted > 0) {
            Cache::store('redis')->forget('inventory:summary');
        }

        return $deleted;
    }
}
