<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $sku
 * @property string $name
 * @property int $total_quantity
 * @property float $total_sales
 * @property float $total_cost
 * @property float $total_profit
 * @property string|null $date
 */
class SalesReportResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'sku' => $this->sku,
            'product_name' => $this->name ?? null,
            'total_quantity' => $this->total_quantity,
            'total_sales' => $this->total_sales,
            'total_cost' => $this->total_cost,
            'total_profit' => $this->total_profit,
        ];
    }
}
