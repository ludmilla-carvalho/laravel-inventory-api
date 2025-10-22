<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
interface BaseRepositoryInterface
{
    /**
     * @return Collection<int, TModel>
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * @return TModel|null
     */
    public function find(int $id, array $columns = ['*']);

    /**
     * @param  array<string, mixed>  $data
     * @return TModel
     */
    public function create(array $data);

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
