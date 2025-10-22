<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $sku
 * @property string $name
 * @property string|null $description
 * @property float $cost_price
 * @property float $sale_price
 * @property int $quantity
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
            'cost_price' => $this->cost_price,
            'sale_price' => $this->sale_price,
            'quantity' => $this->quantity,
            'last_updated' => $this->last_updated
                ? \Carbon\Carbon::parse($this->last_updated)->format('Y-m-d H:i:s')
                : null,
        ];
    }
}
