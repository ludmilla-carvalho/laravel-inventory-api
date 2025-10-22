<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $sku
 * @property string|null $product_name
 * @property int $total_sold
 * @property float $total_revenue
 * @property float $total_cost
 * @property float $total_profit
 * @property string|null $start_date
 * @property string|null $end_date
 */
class SalesReportResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'sku' => $this->sku,
            'product_name' => $this->product_name ?? null,
            'total_sold' => $this->total_sold,
            'total_revenue' => $this->total_revenue,
            'total_cost' => $this->total_cost,
            'total_profit' => $this->total_profit,
            'start_date' => $this->start_date ?? null,
            'end_date' => $this->end_date ?? null,
        ];
    }
}
