<?php

namespace App\Repositories\Contracts;

use App\Models\Product;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySku(string $sku): ?Product;
}
