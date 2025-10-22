<?php

namespace App\Repositories\Contracts;

use App\Models\Inventory;

interface InventoryRepositoryInterface extends BaseRepositoryInterface
{
    public function findByProductId(int $productId): ?Inventory;

    public function deleteOlderThan(\DateTimeInterface $date): int;
}
