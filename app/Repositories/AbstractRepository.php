<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class AbstractRepository
{
    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $model;

    /**
     * @var Model
     */
    protected $initModel;

    public function __construct()
    {
        $this->init();
    }

    /**
     * return model name.
     */
    abstract public function model(): string;

    /**
     * {@inheritdoc}
     */
    final public function getTable(): string
    {
        return $this->initModel->getTable();
    }

    /**
     * {@inheritdoc}
     */
    final public function getKeyName(): string
    {
        return $this->initModel->getKeyName();
    }

    /**
     * @param array|string $with
     */
    final public function get(
        array $filter = [],
        $with = null,
        array $columns = ['*'],
        string $order = '',
        string $direction = 'desc'
    ): \Illuminate\Support\Collection
    {
        $this->reset();

        if ($with) {
            $this->with($with);
        }

        if (!$order) {
            $order = $this->getKeyName();
        }

        $this->orderBy($order, $direction);

        $this->filter($filter);

        return $this->model->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    final public function filter(array $args = [])
    {
        if (method_exists($this->initModel, 'modelFilter')) {
            $this->model = $this->model->filter($args);
        } else {
            foreach ($args as $k => $v) {
                if (is_string($v) || is_numeric($v)) {
                    $this->model = $this->model->where($k, $v);
                } elseif (is_array($v)) {
                    $this->model = $this->model->whereIn($k, $v);
                }
            }
        }

        return $this;
    }

    /**
     * @param array|string $with
     */
    final public function paginate(
        array $filter = [],
        $with = null,
        int $limit = 15,
        array $columns = ['*'],
        string $order = '',
        string $direction = 'desc'
    )
    {
        $this->reset();

        if ($with) {
            $this->with($with);
        }

        if (!$order) {
            $order = $this->getKeyName();
        }

        $this->orderBy($order, $direction);
        $this->filter($filter);

        $result = $this->model->paginate($limit, $columns)->appends(request()->all());
        $page_info = [
            'count' => $result->total(),
            'pages' => ceil($result->total() / $limit),
            'current_page' => $result->currentPage(),
            'limit' => $limit
        ];

        return ['list' => $result->items(), 'page_info' => $page_info];
    }

    /**
     * {@inheritdoc}
     */
    final public function reset(): void
    {
        static::make();
    }

    /**
     * {@inheritdoc}
     */
    final public function all(array $columns = ['*']): \Illuminate\Support\Collection
    {
        $results = $this->model->get($columns);

        $this->reset();

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    final public function pluck(string $column): \Illuminate\Support\Collection
    {
        $results = $this->model->pluck($column);

        $this->reset();

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function firstOrCreate(array $target, array $data = []): \Illuminate\Database\Eloquent\Model
    {
        return $this->model->firstOrCreate($target, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateOrCreate(array $target, array $data): \Illuminate\Database\Eloquent\Model
    {
        return $this->model->updateOrCreate($target, $data);
    }

    /**
     * {@inheritdoc}
     */
    final public function firstOrFail(array $columns = ['*']): \Illuminate\Database\Eloquent\Model
    {
        $result = $this->model->firstOrFail($columns);

        $this->reset();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    final public function latest()
    {
        $this->model = $this->model->latest('created_at');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    final public function take(int $count = 5)
    {
        $this->model = $this->model->take($count);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    final public function first(array $columns = ['*']): ?\Illuminate\Database\Eloquent\Model
    {
        $result = $this->model->first($columns);

        $this->reset();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    final public function count(string $column = '*'): int
    {
        $result = $this->model->count($column);

        $this->reset();

        return $result;
    }

    /**
     * @param array|string $relations
     */
    final public function withCount($relations)
    {
        if (!$relations) {
            return $this;
        }

        $this->model = $this->model->withCount($relations);

        return $this;
    }

    /**
     * @param array|string $relations
     */
    final public function with($relations)
    {
        if (!$relations) {
            return $this;
        }

        if (!is_array($relations)) {
            $relations = [$relations];
        }

        $this->model = $this->model->with($relations);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    final public function orderBy(string $column, string $direction = 'asc')
    {
        $this->model = $this->model->orderBy($column, $direction);

        return $this;
    }

    final public function orderByRaw(string $sql, array $bindings = [])
    {
        $this->model = $this->model->orderByRaw($sql, $bindings);

        return $this;
    }

    /**
     * @param Model|int $model_or_id
     */
    public function update($model_or_id, array $data = []): \Illuminate\Database\Eloquent\Model
    {
        $instance = $this->find($model_or_id);

        $instance->update($data);

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): \Illuminate\Database\Eloquent\Model
    {
        return $this->model->create($data);
    }

    /**
     * @param int|string $value
     */
    final public function findBy(string $field, $value, array $columns = ['*']): \Illuminate\Database\Eloquent\Model
    {
        $instance = $this->model->where($field, $value)->firstOrFail($columns);

        $this->reset();

        return $instance;
    }

    /**
     * @param Model|int $model_or_id
     */
    public function find($model_or_id, array $columns = ['*']): \Illuminate\Database\Eloquent\Model
    {
        if (is_object($model_or_id)) {
            //we'll just check if it's an instance of the model class
            //no need to check the primary key if they pass an instance.
            if ($model_or_id instanceof $this->initModel) {
                return $model_or_id;
            }

            throw (new ModelNotFoundException())->setModel($this->model(), 0);
        }

        $instance = $this->model->findOrFail($model_or_id, $columns);

        $this->reset();

        return $instance;
    }

    /**
     * @param array|string $model_or_id
     *
     * @throws \Exception
     */
    public function delete($model_or_id): bool
    {
        if (is_array($model_or_id)) {
            return $this->model->whereIn('id', $model_or_id)->delete();
        }
        try {
            $model = $this->model->find($model_or_id);
        } catch (ModelNotFoundException $e) {
            return false;
        }

        $this->reset();

        return $model->delete();
    }

    /**
     * {@inheritdoc}
     */
    final public function whereDoesntHave(string $relation, \Closure $closure)
    {
        $this->model = $this->model->whereDoesntHave($relation, $closure);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    final public function whereIn(array $keys)
    {
        if (!isset($keys[0])) {
            $key = key($keys);
            $ids = current($keys);
        } else {
            $key = $this->getKeyName();
            $ids = $keys;
        }

        $this->model = $this->model->whereIn($key, $ids);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    final public function whereHas(string $relation, \Closure $closure)
    {
        $this->model = $this->model->whereHas($relation, $closure);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    final public function sum(string $field): int
    {
        $sum = $this->model->sum($field);

        $this->reset();

        return $sum;
    }

    /**
     * {@inheritdoc}
     */
    final public function withTrashed()
    {
        $this->model = $this->model->withTrashed();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    final public function insert(array $values): bool
    {
        $this->reset();

        return $this->model->insert($values);
    }

    protected function make(): void
    {
        $this->model = $this->initModel = app($this->model());
    }

    /**
     * init the repositoriy.
     */
    private function init(): void
    {
        $this->make();
    }
}
