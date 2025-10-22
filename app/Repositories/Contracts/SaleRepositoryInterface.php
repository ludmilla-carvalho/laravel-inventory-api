<?php

namespace App\Repositories\Contracts;

use App\Models\Sale;
use Illuminate\Support\Collection;

/**
 * @extends BaseRepositoryInterface<Sale>
 */
interface SaleRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return Collection<int, Sale>
     */
    public function findByPeriod(string $startDate, string $endDate): Collection;
}
