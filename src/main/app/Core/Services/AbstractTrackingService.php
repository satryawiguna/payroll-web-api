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

namespace App\Core\Services;

use App\Core\Auth\UserPrincipal;
use App\Core\Repositories\AbstractRepository;
use App\Core\Repositories\RepositoryTrait;
use App\Core\Repositories\TrackingRepositoryTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property AbstractRepository | RepositoryTrait | TrackingRepositoryTrait $repo
 */
abstract class AbstractTrackingService extends BaseService
{

    function getPage(Carbon $effective, array $criteria, UserPrincipal $user, ?array $options = null): array
    {
        $q = $this->repo->getAll($user->company_id, $effective, $criteria, $options);
        return $this->paginate($q, $criteria['per_page']);
    }

    function getOne($id, Carbon $effective, ?array $columns, UserPrincipal $user, ?array $options = null): ?object
    {
        $q = $this->repo->getOne($id, $user->company_id, $effective, $columns, $options);
        return $q->first();
    }

    /**
     * Insert data record baru.
     */
    function insert(array $data, UserPrincipal $user): object
    {
        $this->validateInsert($data, $user);

        $effectiveStart = str_to_date($data['effective_start'] ?? null);
        $effectiveEnd = str_to_date($data['effective_end'] ?? null);

        return $this->_insert($effectiveStart, $effectiveEnd, function($id, $effective) use ($data, $user) {
            if ($id[0] === null) $id[0] = generate_id();
            $toInsert = $this->getDataForInsert($data, null, $id, $effective, $user);
            $this->repo->insert($toInsert);
            return (object) ['new_id' => $id[0]];
        });
    }

    protected function _insert(?Carbon $newEffectiveStart, ?Carbon $newEffectiveEnd, \Closure $block): object
    {
        DB::beginTransaction();
        try {
            $start = $newEffectiveStart ?: str_to_date(BOT);
            $end = $newEffectiveEnd ?: str_to_date(EOT);
            $this->validateEffectiveDate($start, $end);

            $ret = $block([null, $start], (object) ['first' => BOT, 'start' => $start, 'end' => $end, 'last' => EOT]);
            DB::commit();
            return $ret;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function validateEffectiveDate(Carbon $start, Carbon $end)
    {
        if ($start->isAfter($end)) {
            abort(400, "Invalid effective date, start > end", 'common:error.invalid-effective-date');
        }
    }

    protected function getDataForInsert(array $data, ?object $existing, array $id, object $effective, UserPrincipal $user): array
    {
        if ($existing !== null) {
            foreach ($existing as $key => $value) {
                if (!array_key_exists($key, $data)) $data[$key] = $value;
            }
        }
        $data[$this->getPrimaryKey()] = $id[0];
        $data['company_id'] = $user->company_id;
        $data['effective_first'] = date_to_str($effective->first);
        $data['effective_start'] = date_to_str($effective->start);
        $data['effective_end'] = date_to_str($effective->end);
        $data['effective_last'] = date_to_str($effective->last);
        $data['created_by'] = $user->username;
        return $data;
    }

    /**
     * Update data. Insert new history jika <code>changeInsert = true</code>.
     */
    function update($id, Carbon $effective, array $data, UserPrincipal $user, bool $changeInsert): ?object
    {
        $existing = $this->repo->getExisting($id, $user->company_id, $effective);
        $this->validateUpdate($data, $existing, $user);

        $newEffectiveStart = str_to_date($data['effective_start'] ?? BOT);
        $newEffectiveEnd = str_to_date($data['effective_end'] ?? EOT);

        return $this->_update($existing, $newEffectiveStart, $newEffectiveEnd, $changeInsert,
            function(array $newId, object $newEffective, bool $isNew) use ($id, $data, $existing, $user) {
                if ($isNew) {
                    if ($newId[0] === null) $newId[0] = generate_id();
                    $toInsert = $this->getDataForInsert($data, $existing, $newId, $newEffective, $user);
                    $this->repo->insert($toInsert);
                } else {
                    $toUpdate = $this->getDataForUpdate($data, $existing, $newId, $newEffective, $user);
                    $this->repo->updateByTrackingId($newId, $toUpdate);
                }
                return (object) ['count' => 1, 'new_history' => $isNew];
            }
        );
    }

    protected function _update(object $existing, ?Carbon $newEffectiveStart, ?Carbon $newEffectiveEnd,
                               bool $changeInsert, \Closure $block): ?object
    {
        DB::beginTransaction();
        $pk = $this->getPrimaryKey();
        try {
            $newId = [$existing->{$pk}, str_to_date($existing->effective_start)];
            $id = [$existing->{$pk}, str_to_date($existing->effective_start)];
            $effective = (object) [
                'first' => str_to_date($existing->effective_first),
                'start' => str_to_date($existing->effective_start),
                'end' => str_to_date($existing->effective_end),
                'last' => str_to_date($existing->effective_last),
            ];

            if ($newEffectiveStart === null && $newEffectiveEnd === null) {
                return $block($newId, $effective, false);
            }

            $start = $newEffectiveStart ?? $effective->start;
            $end = $newEffectiveEnd ?? $effective->end;
            $this->validateEffectiveDate($start, $end);

            $moved = ($start->isBefore($effective->start) && $end->isBefore($effective->end))
                || ($start->isAfter($effective->end) && $end->isAfter($effective->end));

            // Jika move ke kiri dan new effective end adalah effective end dari previous
            // maka update existing effective start untuk menghindari duplicate data
            if (is_same_date($end, minus_day($effective->start)) && is_bot($effective->first)) {
                $this->repo->deleteHistory([$id[0], $start]);
                $this->repo->updateEffectiveStart($id, $start);
                $newId = [$id[0], $start];
            }

            // Update effective start (left side)
            [$effectiveFirst, $isInsert] = $this->_updateEffectiveStart($id, $effective, $start, $moved, $changeInsert);

            // Update effective end (right side)
            $effectiveLast = $this->_updateEffectiveEnd($id, $effective, $end, $moved);

            // Delete overlap items
            $this->repo->deleteInBetween($id[0], $start, $end, $id[1], $newId[1]);

            $newEffective = (object) ['first' => $effectiveFirst, 'start' => $start, 'end' => $end, 'last' => $effectiveLast];
            $ret = $block($newId, $newEffective, $isInsert);
            DB::commit();
            return $ret;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function _updateEffectiveStart(array $id, object $effective, Carbon $newEffectiveStart, bool $moved,
                                           bool $changeInsert): array
    {
        if (is_same_date($newEffectiveStart, $effective->start)) {
            return [$effective->first, false];
        }

        $overlap = $this->repo->prevIsOverlap($id[0], $effective->start, $newEffectiveStart);

        if ($newEffectiveStart->isBefore($effective->start) && !$overlap && !$moved) {
            $target = [$id, $effective];
        } else {
            $target = $this->repo->getAtEffective($id[0], $newEffectiveStart);
        }

        if (is_same_date($newEffectiveStart, plus_day($effective->end))) {
            $prev = ($changeInsert || !is_bot($effective->first)) ? [$id, $effective] : null;
        } else if ($overlap || $moved || ($changeInsert && $newEffectiveStart->isAfter($effective->start))) {
            if ($target === null || is_same_date($target[1]->start, $newEffectiveStart)) {
                $t = $this->repo->getPrev($id[0], $newEffectiveStart);
                $prev = (is_bot($effective->first) && $t !== null && is_same_date($t[1]->start, $id[1])) ? null : $t;
            } else {
                $prev = $target;
            }
        } else {
            $d = ($newEffectiveStart->isAfter($effective->start)) ? $effective->start : $newEffectiveStart;
            $prev = $this->repo->getPrev($id[0], $d);
        }

        if (is_bot($effective->first) && $newEffectiveStart->isAfter($effective->start)) {
            $d = plus_day($effective->end);
            $this->repo->updateEffectiveStart([$id[0], $d], $d, str_to_date(BOT));
        }

        if ($prev !== null) {
            $this->repo->updateEffectiveEnd([$prev[0], $prev[1]->start], minus_day($newEffectiveStart));
        }

        $isInsert = $changeInsert && ((
            $newEffectiveStart->isAfter($effective->start) &&
            $target !== null && is_same_date($id[1], $target[1]->start)
        ) || (
            is_same_date($newEffectiveStart, plus_day($effective->end)) &&
            is_bot($effective->first)
        ));
        return [($prev !== null) ? $newEffectiveStart : str_to_date(BOT), $isInsert];
    }

    private function _updateEffectiveEnd(array $id, object $effective, Carbon $newEffectiveEnd, bool $moved): Carbon
    {
        if (is_same_date($newEffectiveEnd, $effective->end)) {
            return $effective->last;
        }

        $overlap = $this->repo->nextIsOverlap($id[0], $effective->end, $newEffectiveEnd);

        if (!$overlap && $newEffectiveEnd->isAfter($effective->start)) {
            $n = $this->repo->getNext($id[0], $effective->end);
            $next = ($n !== null && minus_day($n[1]->end)->isBefore($newEffectiveEnd)) ? null : $n;
        } else {
            if ($moved && !is_bot($effective->first)) {
                $this->repo->updatePrevEffectiveEnd($id[0], $effective);
            }
            if (!is_bot($effective->first) || !is_eot($effective->last)) {
                $next = $this->repo->getAtEffective($id[0], plus_day($newEffectiveEnd));
                if ($next === null) $next = $this->repo->getNext($id[0], $newEffectiveEnd);
            } else {
                $next = null;
            }
        }

        if ($next !== null) {
            $this->repo->updateEffectiveStart([$next[0], $next[1]->start], plus_day($newEffectiveEnd));
        }
        return ($next !== null) ? $newEffectiveEnd : str_to_date(EOT);
    }

    protected function getDataForUpdate(array $data, ?object $existing, array $id, object $effective, UserPrincipal $user): array
    {
        if ($existing !== null) {
            foreach ($existing as $key => $value) {
                if (!array_key_exists($key, $data)) $data[$key] = $value;
            }
        }
        $data['updated_by'] = $user->username;
        $data['effective_first'] = date_to_str($effective->first);
        $data['effective_start'] = date_to_str($effective->start);
        $data['effective_end'] = date_to_str($effective->end);
        $data['effective_last'] = date_to_str($effective->last);
        return $data;
    }

    /**
     * Delete end date jika effective tidak null, atau delete semua history jika null
     */
    function delete($id, ?Carbon $effective, UserPrincipal $user): object
    {
        $existing = $this->repo->getExisting($id, $user->company_id, $effective ?? Carbon::now());
        if ($existing === null) abort(404, "NO data to delete");

        $this->validateDelete($existing, $user);

        DB::beginTransaction();
        try {
            if ($effective !== null && str_to_date($existing->effective_start)->isBefore($effective)) {
                $this->repo->updateEffectiveEnd([$id, $existing->effective_start], minus_day($effective), str_to_date(EOT));
                $this->repo->deleteInBetween($id, $effective, minus_day($effective), str_to_date($existing->effective_start));
            } else {
                $this->repo->deleteById($id);
            }
            DB::commit();
            return (object) ['count' => 1];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

}
