<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface BaseRepositoryInterface
{
    public function all(array $columns = ['*']): Collection;

    public function find(int $id, array $columns = ['*']): ?Model;

    public function create(array $data): Model;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
