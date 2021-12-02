<?php
/*
 * Copyright (c) 2021 All Rights Reserved.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Proprietary and confidential.
 *
 * Written by:
 *   - Satrya Wiguna <satrya@freshcms.net>
 */

namespace App\Core\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * @property Model $model
 */
trait RepositoryTrait
{

    /**
     * Params: $companyId, $effective, $criteria[columns, search, filters, sorts], $options.
     */
    function getAll(): Builder
    {
        [$companyId, $effective, $criteria, $options] = $this->argsQuery(func_get_args());
        return $this->inquiry($companyId, $effective, $criteria, $options);
    }

    /**
     * Params: $id, $companyId, $effective, $columns.
     */
    function getOne(): ?Builder
    {
        [$id, $companyId, $effective, $columns] = $this->argsGetOne(func_get_args());
        $criteria = ($columns !== null) ? ['columns' => $columns] : null;
        return $this
            ->inquiry($companyId, $effective, $criteria)
            ->where('_.'.$this->getPrimaryKey(), $id);
    }

    protected function inquiry($companyId, ?Carbon $effective, ?array $criteria, ?array $options = null): Builder
    {
        return $this->query($companyId, $effective, $criteria, $options);
    }

    /**
     * Params: $id, $companyId, $effective.
     */
    function getExisting(): ?object
    {
        [$id, $companyId, $effective] = $this->argsGetOne(func_get_args());
        return $this
            ->query($companyId, $effective, ['columns' => '*', 'sorts' => []])
            ->where($this->getPrimaryKey(), $id)
            ->first();
    }

    protected function argsGetOne(array $args): array
    {
        $id = $args[0];
        $companyId = $args[1] ?? null;
        $effective = $args[2] ?? null;
        $columns = $args[3] ?? null;
        $options = $args[4] ?? [];
        if (is_array($effective)) {
            $columns = $effective;
            $effective = null;
            $options = $args[3] ?? [];
        }
        return [$id, $companyId, $effective, $columns, $options];
    }

    /**
     * Params: $companyId, $effective, $criteria[columns, search, filters, sorts], $options.
     *
     * filters adalah list yg terdiri dari:
     * ```
     * {
     *   // untuk filter condition
     *   field: string,
     *   operator: string     <-- optional, default '='
     *   value: mixed
     *
     *   // untuk nested operation and/or
     *   operation: string    <-- optional, default 'and'
     *   items: array         <-- array of self
     * }
     * ```
     */
    function query(): Builder
    {
        [$companyId, $effective, $criteria, $options] = $this->argsQuery(func_get_args());

        $q = DB::table($this->getTable().' as _');

        // columns
        $columns = $this->getColumns($criteria);
        $q->select($this->normalizeColumns($columns));

        // where
        if ($companyId !== null) {
            $q->where(function(Builder $q) use ($companyId) {
                $q->orWhereNull('_.company_id');
                $q->orWhere('_.company_id', $companyId);
            });
        }
        if ($effective !== null) {
            if ($options['include-all'] ?? true) {
                $q->whereRaw("? between _.effective_first and _.effective_last", date_to_str($effective));
            } else {
                $q->whereRaw("? between _.effective_start and _.effective_end", date_to_str($effective));
            }
        }

        $search = $criteria['search'] ?? null;
        $this->addSearchFilter($q, $search);

        $filters = (isset($criteria['filters']) && is_array($criteria['filters'])) ? $criteria['filters'] : [];
        $this->addWhereFilter($q, $filters);

        // sort order
        $sorts = $this->getSorts($criteria);
        if (!empty($sorts)) {
            foreach ($sorts as $sort) {
                $s = explode(' ', $sort);
                $field = $this->normalizeField($s[0], false);
                $direction = (count($s) > 1) ? $s[1] : 'asc';
                $q->orderBy($field, $direction);
            }
        }

        if ($this->hasSoftDelete()) {
            $q->whereNull('_.deleted_at');
        }
        return $q;
    }

    protected function getColumns(array $criteria): array
    {
        if (!empty($criteria['columns'])) {
            return is_array($criteria['columns']) ? $criteria['columns'] : [$criteria['columns']];
        }
        if (property_exists($this->model, 'selectable')) {
            return $this->model->selectable;
        }
        return [];
    }

    protected function getSorts(array $criteria): array
    {
        $sorts = [];
        if (array_key_exists('sorts', $criteria)) {
            $sorts = is_array($criteria['sorts']) ? $criteria['sorts'] : [$criteria['sorts']];
        } else if (property_exists($this->model, 'sortable')) {
            $sorts = $this->model->sortable;
        }
        return $sorts;
    }

    protected function argsQuery(array $args): array
    {
        $companyId = $args[0] ?? null;
        $effective = $args[1] ?? null;
        $criteria = $args[2] ?? [];
        $options = $args[3] ?? [];
        if (is_array($companyId)) { // criteria, options
            $criteria = $companyId;
            $effective = null;
            $companyId = null;
            $options = $args[1] ?? [];
        } else if (is_array($effective)) { // companyId, criteria, options | effective, criteria, options
            $criteria = $effective;
            if ($companyId instanceof Carbon) {
                $effective = $companyId;
                $companyId = null;
            } else {
                $effective = null;
            }
            $options = $args[2] ?? [];
        }
        return [$companyId, $effective, $criteria, $options];
    }

    private function normalizeColumns($columns, bool $addAlias = true): array
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }
        if (empty($columns)) {
            return ['*'];
        }
        return array_map(function($field) use ($addAlias) {
            return $this->normalizeField($field, $addAlias);
        }, $columns);
    }

    private function normalizeField($fieldName, bool $addAlias = true)
    {
        if (is_string($fieldName)) {
            if (str_starts_with($fieldName, 'RAW:')) {
                return DB::raw(trim(substr($fieldName, 4)));
            }
            if (!$addAlias) return $fieldName;
            return !str_contains($fieldName, '.') ? '_.'.$fieldName : $fieldName;
        }
        return $fieldName;
    }

    private function addSearchFilter(Builder $q, ?string $search)
    {
        if (empty($search)) return;
        if (!property_exists($this->model, 'searchable')) {
            abort(500, 'Searchable columns must be defined');
        }
        $searchPattern = '%'.str_replace(' ', '%', str_replace('%', '\%', $search)).'%';
        $q->where(function(Builder $q) use ($searchPattern) {
            foreach ($this->model->searchable as $field) {
                $q->orWhere($this->normalizeField($field), 'like', $searchPattern);
            }
        });
    }

    private function addWhereFilter(Builder $q, array $filters, string $boolean = 'and')
    {
        foreach ($filters as $filter) {
            if (isset($filter['items']) && is_array($filter['items'])) {
                $q->where(function(Builder $q) use ($filter) {
                    $operation = isset($filter['operation']) ? strtolower($filter['operation']) : 'and';
                    $this->addWhereFilter($q, $filter['items'], $operation);
                }, null, null, $boolean);
                return;
            }

            $field = $this->normalizeField($filter['field']);
            $operator = isset($filter['operator']) ? strtolower($filter['operator']) : '=';
            $value = $filter['value'];
            if (is_sequential_array($value)) {
                $not = $operator == '<>' || $operator == '!=' || $operator == 'not in';
                $q->whereIn($field, $value, $boolean, $not);
            } else if ($value === null) {
                $q->whereNull($field, $boolean);
            } else if ($operator == 'like') {
                $pattern = '%'.str_replace(' ', '%', str_replace('%', '\%', $value)).'%';
                $q->where($field, $operator, $pattern, $boolean);
            } else {
                $q->where($field, $operator, $value, $boolean);
            }
        }
    }

    function insert(array $data): ?int
    {
        $bulk = is_sequential_array($data);
        if ($bulk) {
            $toInsert = [];
            foreach ($data as $item) {
                $toInsert[] = $this->getFillableData($item, true, false);
            }
        } else {
            $toInsert = $this->getFillableData($data, true, false);
        }
        if ($bulk) {
            DB::table($this->getTable())->insert($toInsert);
            return null;
        }
        return DB::table($this->getTable())->insertGetId($toInsert);
    }

    function updateById($id, array $data): int
    {
        return $this->update($data, [$this->getPrimaryKey() => $id]);
    }

    function updateByTrackingId(array $id, array $data): int
    {
        return $this->update($data, [$this->getPrimaryKey() => $id[0], 'effective_start' => date_to_str($id[1])]);
    }

    function update(array $data, array $condition): int
    {
        if (is_sequential_array($data)) {
            $toUpdate = [];
            foreach ($data as $item) {
                $toUpdate[] = $this->getFillableData($item, true, false);
            }
        } else {
            $toUpdate = $this->getFillableData($data, true, false);
        }
        $q = DB::table($this->getTable());
        foreach ($condition as $field => $value) {
            $q->where($field, $value);
        }
        return $q->update($toUpdate);
    }

    private function getFillableData(array $data, bool $fillCreatedAt, bool $fillUpdatedAt): array {
        $fillable = $this->model->getFillable();

        $ret = [];
        foreach ($data as $field => $value) {
            if (in_array(strtolower($field), $fillable)) {
                $ret[$field] = $value;
            }
        }
        if ($fillCreatedAt) $ret['created_at'] = now();
        if ($fillUpdatedAt) $ret['updated_at'] = now();

        return $ret;
    }

    function delete(string $where, ...$params): int
    {
        return DB::table($this->getTable())->whereRaw($where, $params)->delete();
    }

    function deleteById($id): int
    {
        return $this->delete($this->getPrimaryKey().' = ?', $id);
    }

    function getTable(?string $alias = null): string
    {
        $table = $this->model->getTable();
        return ($alias !== null) ? $table.' as '.$alias : $table;
    }

    function getPrimaryKey(): string
    {
        return $this->model->getKeyName();
    }

    function hasSoftDelete(): bool
    {
        return method_exists($this->model, 'trashed');
    }
}
