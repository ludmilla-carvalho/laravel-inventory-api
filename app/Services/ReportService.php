<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ReportService
{
    public function __construct(

    ) {}

    public function salesReport(?string $startDate, ?string $endDate, ?string $productSku = null)
    {
        $query = DB::table('sales')
            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select(
                'products.sku',
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.quantity * sale_items.unit_price) as total_sales'),
                DB::raw('SUM(sale_items.quantity * sale_items.unit_cost) as total_cost'),
                DB::raw('SUM((sale_items.quantity * sale_items.unit_price) - (sale_items.quantity * sale_items.unit_cost)) as total_profit')
            )
            ->groupBy('products.sku', 'products.name');

        if ($startDate && $endDate) {
            $query->whereBetween('sales.created_at', [$startDate, $endDate]);
        }

        if ($productSku) {
            $query->where('products.sku', $productSku);
        }

        return $query->get();
    }
}
