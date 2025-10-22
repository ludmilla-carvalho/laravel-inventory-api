<?php

namespace App\Services;

use App\Enums\SaleStatus;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\SaleItemRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use Illuminate\Support\Facades\DB;

// use Illuminate\Support\Facades\Event;
// use App\Events\SaleFinalized;

class SalesService
{
    public function __construct(
        private SaleRepositoryInterface $saleRepository,
        private SaleItemRepositoryInterface $saleItemRepository,
        private ProductRepositoryInterface $productRepository
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

            // TODO: Dispara evento para processar estoque em fila
            // Event::dispatch(new SaleFinalized($sale));

            return $sale;
        });
    }

    public function getSaleDetails(int $id)
    {
        return $this->saleRepository->find($id)?->load('items.product');
    }
}
