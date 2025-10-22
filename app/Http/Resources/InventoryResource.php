<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $sku
 * @property string $name
 * @property string|null $description
 * @property int $quantity
 * @property float $cost_price
 * @property float $sale_price
 * @property float $total_cost
 * @property float $projected_profit
 * @property string|null $last_updated
 */
class InventoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'sku' => $this->sku,
            'name' => $this->name,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'cost_price' => $this->cost_price,
            'sale_price' => $this->sale_price,
            'total_cost' => $this->total_cost,
            'projected_profit' => $this->projected_profit,
            'last_updated' => $this->last_updated
                ? \Carbon\Carbon::parse($this->last_updated)->format('Y-m-d H:i:s')
                : null,
        ];
    }
}
