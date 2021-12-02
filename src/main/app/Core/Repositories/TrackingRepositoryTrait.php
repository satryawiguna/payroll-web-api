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
trait TrackingRepositoryTrait
{
    use RepositoryTrait;

    function getAtEffective($id, Carbon $effective): ?array
    {
        $ret = $this->selectTracking($id)
            ->where('effective_start', '<=', date_to_str($effective))
            ->where('effective_end', '>=', date_to_str($effective))
            ->first();
        return $this->getTrackingResult($ret);
    }

    private function selectTracking($id): Builder
    {
        $pk = $this->getPrimaryKey();
        return DB::table($this->model->getTable())
            ->select($pk, 'effective_first', 'effective_start', 'effective_end', 'effective_last')
            ->where($pk, $id);
    }

    private function getTrackingResult(?object $data): ?array
    {
        if ($data === null) return null;
        return [
            $data->{$this->getPrimaryKey()},
            (object) [
                'first' => str_to_date($data->effective_first),
                'start' => str_to_date($data->effective_start),
                'end' => str_to_date($data->effective_end),
                'last' => str_to_date($data->effective_last),
            ],
        ];
    }

    function getPrev($id, Carbon $effective): ?array
    {
        if (is_bot($effective)) return null;
        $ret = $this->selectTracking($id)
            ->where('effective_start', "<=", date_to_str(minus_day($effective)))
            ->orderBy('effective_start', 'desc')
            ->first();
        return $this->getTrackingResult($ret);
    }

    function getNext($id, Carbon $effective): ?array
    {
        if (is_eot($effective)) return null;

        $ret = $this->selectTracking($id)
            ->where('effective_start', '>=', date_to_str(plus_day($effective)))
            ->orderBy('effective_start')
            ->first();
        return $this->getTrackingResult($ret);
    }

    function prevIsOverlap($id, Carbon $currEffectiveStart, Carbon $newEffectiveStart): bool
    {
        $count = $this->model
            ->where($this->getPrimaryKey(), $id)
            ->where('effective_end', '<', date_to_str($currEffectiveStart))
            ->where('effective_end', '>=', date_to_str(minus_day($newEffectiveStart)))
            ->count();
        return $count > 1;
    }

    function nextIsOverlap($id, Carbon $currEffectiveEnd, Carbon $newEffectiveEnd): bool
    {
        $count = $this->model
            ->where($this->getPrimaryKey(), $id)
            ->where('effective_start', '>', date_to_str($currEffectiveEnd))
            ->where('effective_start', '<=', date_to_str(plus_day($newEffectiveEnd)))
            ->count();
        return $count > 1;
    }

    function updateEffectiveStart(array $id, Carbon $effectiveStart, ?Carbon $effectiveFirst = null)
    {
        $this->model
            ->where($this->getPrimaryKey(), $id[0])
            ->where('effective_start', date_to_str($id[1]))
            ->update([
                'effective_start' => date_to_str($effectiveStart),
                'effective_first' => date_to_str($effectiveFirst ?? $effectiveStart),
            ]);
    }

    function updateEffectiveEnd(array $id, Carbon $effectiveEnd, ?Carbon $effectiveLast = null)
    {
        $this->model
            ->where($this->getPrimaryKey(), $id[0])
            ->where('effective_start', date_to_str($id[1]))
            ->update([
                'effective_end' => date_to_str($effectiveEnd),
                'effective_last' => date_to_str($effectiveLast ?? $effectiveEnd),
            ]);
    }

    function deleteHistory(array $id)
    {
        $this->model->where($this->getPrimaryKey(), $id[0])->where('effective_start', date_to_str($id[1]))->delete();
    }

    function deleteInBetween($id, Carbon $start, Carbon $end, Carbon ...$excludes)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        $q = $this->model
            ->where($this->getPrimaryKey(), $id)
            ->where(function($q) use ($start, $end) {
                $q->orWHere(function($q) use ($start, $end) {
                    $q->where('effective_end', '>=', date_to_str($start));
                    $q->where('effective_start', '<=', date_to_str($end));
                });
                $q->orWHereRaw('effective_end < effective_start');
            });

        foreach ($excludes as $exclude) {
            $q->where('effective_start', '<>', date_to_str($exclude));
        }
        $q->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    function updatePrevEffectiveEnd($id, object $effective)
    {
        $this->model
            ->where($this->getPrimaryKey(), $id)
            ->where('effective_end', date_to_str(minus_day($effective->start)))
            ->update([
                'effective_end' => date_to_str($effective->end),
                'effective_last' => date_to_str($effective->last),
            ]);
    }
}
