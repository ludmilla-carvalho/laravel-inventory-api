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

    public function findById(int $id): ?Sale;

    public function findByIdWithItems(int $id): ?Sale;

    // todo: verificar se é usado
    // public function lockForUpdate(int $id): ?Sale;
}
