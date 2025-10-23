<?php

namespace App\Repositories;

use App\Models\Sale;
use App\Repositories\Contracts\SaleRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * @extends BaseRepository<Sale>
 */
class SaleRepository extends BaseRepository implements SaleRepositoryInterface
{
    public function __construct(Sale $model)
    {
        parent::__construct($model);
    }

    /** @return Collection<int, Sale> */
    public function findByPeriod(string $startDate, string $endDate): Collection
    {
        return Sale::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
    }

    public function findById(int $id): ?Sale
    {
        return Sale::find($id);
    }

    public function findByIdWithItems(int $id): ?Sale
    {
        return Sale::with('items.product')->find($id);
    }

    // todo: verificar se é usado
    // public function lockForUpdate(int $id): ?Sale
    // {
    //     return Sale::where('id', $id)->lockForUpdate()->first();
    // }
}
