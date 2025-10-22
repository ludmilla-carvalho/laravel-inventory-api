<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;;
use App\Models\Product;

/**
 * @property int $quantity
 * @property float $unit_price
 * @property-read Product $product
 */
class SaleItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'sku'       => $this->product->sku,
            'name'      => $this->product->name,
            'quantity'  => $this->quantity,
            'unit_price'=> $this->unit_price,
            'total'     => $this->quantity * $this->unit_price,
        ];
    }
}
