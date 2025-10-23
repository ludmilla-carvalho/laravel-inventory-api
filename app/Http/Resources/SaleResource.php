<?php

namespace App\Http\Resources;

use App\Enums\SaleStatus;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property float $total_amount
 * @property float $total_cost
 * @property float $total_profit
 * @property SaleStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class SaleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'total_amount' => sprintf('%.2f', $this->total_amount),
            'total_cost' => sprintf('%.2f', $this->total_cost),
            'total_profit' => sprintf('%.2f', $this->total_profit),
            'status' => $this->status instanceof SaleStatus
                                ? $this->status->name
                                : $this->status,
            'created_at' => $this->created_at?->toDateTimeString(),
            'items' => SaleItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
