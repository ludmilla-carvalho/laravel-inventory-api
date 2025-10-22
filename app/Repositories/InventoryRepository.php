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
}
