<?php

namespace App\Repositories;

use App\Models\SaleItem;
use App\Repositories\Contracts\SaleItemRepositoryInterface;

class SaleItemRepository extends BaseRepository implements SaleItemRepositoryInterface
{
    public function __construct(SaleItem $model)
    {
        parent::__construct($model);
    }
}
