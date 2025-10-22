<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface SaleRepositoryInterface extends BaseRepositoryInterface
{
    public function findByPeriod(string $startDate, string $endDate): Collection;
}
