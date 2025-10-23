<?php

namespace App\Services;

use App\Enums\SaleStatus;
use App\Jobs\ProcessSaleJob;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\SaleItemRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SalesService
{
    public function __construct(
        private SaleRepositoryInterface $saleRepository,
        private SaleItemRepositoryInterface $saleItemRepository,
        private ProductRepositoryInterface $productRepository,
        private InventoryRepositoryInterface $inventoryRepository,
    ) {}

    public function createSale(array $items)
    {
        return DB::transaction(function () use ($items) {
            $sale = $this->saleRepository->create([
                'total_amount' => 0,
                'total_cost' => 0,
                'total_profit' => 0,
                'status' => SaleStatus::Pending,
            ]);

            $totalAmount = 0;
            $totalCost = 0;

            foreach ($items as $item) {
                $product = $this->productRepository->findBySku($item['sku']);

                if (! $product) {
                    throw new \Exception("Produto não encontrado: {$item['sku']}");
                }

                $lineAmount = $product->sale_price * $item['quantity'];
                $lineCost = $product->cost_price * $item['quantity'];

                $this->saleItemRepository->create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->sale_price,
                    'unit_cost' => $product->cost_price,
                ]);

                $totalAmount += $lineAmount;
                $totalCost += $lineCost;
            }

            $sale->update([
                'total_amount' => $totalAmount,
                'total_cost' => $totalCost,
                'total_profit' => $totalAmount - $totalCost,
                'status' => SaleStatus::Processing,
            ]);

            // Despacha para fila assíncrona (Redis)
            ProcessSaleJob::dispatch($sale->id)->onQueue('sales');

            return $sale;
        });
    }

    public function finalizeSale(int $saleId)
    {
        return DB::transaction(function () use ($saleId) {
            /** @var Sale|null $sale */
            $sale = $this->saleRepository->findByIdWithItems($saleId);

            if (! $sale || $sale->status === SaleStatus::Completed) {
                return $sale;
            }

            $totalCost = 0;
            $totalProfit = 0;

            /** @var SaleItem $item */
            foreach ($sale->items as $item) {
                $inventory = $this->inventoryRepository->lockForUpdate($item->product_id);
                if (! $inventory || $inventory->quantity < $item->quantity) {
                    throw new \RuntimeException("Estoque insuficiente para o produto {$item->product_id}");
                }

                // baixa de estoque via repositório
                $this->inventoryRepository->decrementStock($inventory->id, $item->quantity);

                $lineCost = $item->unit_cost * $item->quantity;
                $lineProfit = $item->unit_price * $item->quantity - $lineCost;

                $totalCost += $lineCost;
                $totalProfit += $lineProfit;
            }

            $this->saleRepository->update($sale->id, [
                'total_cost' => $totalCost,
                'total_profit' => $totalProfit,
                'status' => SaleStatus::Completed,
                'processed_at' => now(),
            ]);

            return $sale;
        });
    }

    public function getSaleDetails(int $id)
    {
        return $this->saleRepository->find($id)?->load('items.product');
    }
}
