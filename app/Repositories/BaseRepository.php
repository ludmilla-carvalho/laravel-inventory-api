<?php

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @template TModel of Model
 *
 * @implements BaseRepositoryInterface<TModel>
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    /** @var TModel */
    protected Model $model;

    /**
     * @param  TModel  $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /** @return Collection<int, TModel> */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->all($columns);
    }

    /** @return TModel|null */
    public function find(int $id, array $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }

    /** @return TModel */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $record = $this->find($id);

        return $record ? $record->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $record = $this->find($id);

        return $record ? (bool) $record->delete() : false;
    }
}
