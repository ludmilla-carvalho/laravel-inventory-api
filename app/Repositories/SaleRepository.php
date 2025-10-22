<?php

namespace App\Repositories;

use App\Models\Sale;
use App\Repositories\Contracts\SaleRepositoryInterface;
use Illuminate\Support\Collection;

class SaleRepository extends BaseRepository implements SaleRepositoryInterface
{
    public function __construct(Sale $model)
    {
        parent::__construct($model);
    }

    public function findByPeriod(string $startDate, string $endDate): Collection
    {
        return Sale::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
    }
}
