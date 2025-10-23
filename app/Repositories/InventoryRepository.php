<?php

namespace App\Repositories;

use App\Models\Inventory;
use App\Repositories\Contracts\InventoryRepositoryInterface;

class InventoryRepository extends BaseRepository implements InventoryRepositoryInterface
{
    public function __construct(Inventory $model)
    {
        parent::__construct($model);
    }

    public function findByProductId(int $productId): ?Inventory
    {
        return Inventory::query()->where('product_id', $productId)->first();
    }

    public function deleteOlderThan(\DateTimeInterface $date): int
    {
        return Inventory::query()
            ->where('last_updated', '<', $date)
            ->delete();
    }

    public function lockForUpdate(int $product_id): ?Inventory
    {
        return Inventory::where('product_id', $product_id)->lockForUpdate()->first();
    }

    public function decrementStock(int $id, int $quantity): bool
    {
        $inventory = $this->lockForUpdate($id);

        if (! $inventory || $inventory->quantity < $quantity) {
            return false;
        }

        $inventory->quantity -= $quantity;

        return $inventory->save();
    }
}
