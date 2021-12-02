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

namespace App\Modules\CompensationAdmin\Repositories;

use App\Core\Repositories\AbstractTrackingRepository;
use App\Modules\CompensationAdmin\Models\ElementLinkValue;
use App\Modules\CompensationAdmin\Models\PayrollInputValue;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;

class ElementLinkValueRepository extends AbstractTrackingRepository
{
    protected ElementLinkValue $model;
    protected PayrollInputValueRepository $inputValueRepo;

    function __construct(ElementLinkValue $model, PayrollInputValueRepository $inputValueRepo)
    {
        $this->model = $model;
        $this->inputValueRepo = $inputValueRepo;
    }

    function getPerLink($linkId, $elementId, $companyId, Carbon $effective, ?array $options = null): array
    {
        $criteria = [
            'columns' => [
                'r.value_id', 'r.effective_start', 'r.effective_end',
                '_.input_value_id', '_.value_name as input_value_name', '_.default_value', 'r.link_value',
                'r.description',
            ],
            'sort' => '_.value_name'
        ];
        return $this->inputValueRepo
            ->query($companyId, $effective, $criteria, $options)
            ->leftJoin($this->getTable('r'), function(JoinClause $join) use ($effective, $linkId) {
                $join->where('r.link_id', $linkId);
                $join->on('r.input_value_id', '_.input_value_id');
                $join->whereRaw('? between r.effective_first and r.effective_last', date_to_str($effective));
            })
            ->where('_.element_id', $elementId)
            ->get()->toArray();
    }

    protected function inquiry($companyId, ?Carbon $effective, ?array $criteria, ?array $options = null): Builder
    {
        return $this
            ->query($companyId, $effective, ['sort' => 'iv.value_name'], $options)
            ->join(PayrollInputValue::table('iv'), function(JoinClause $join) use ($effective) {
                $join->on('iv.input_value_id', '_.input_value_id');
                $join->whereRaw('? between iv.effective_first and iv.effective_last', date_to_str($effective));
            });
    }

    function allValues($companyId, Carbon $effective): array
    {
        $ret = [];
        $q = $this->query($companyId, [
            'columns' => ['value_id', 'link_id', 'input_value_id', 'link_value']])
            ->whereRaw('? between _.effective_start and _.effective_end', date_to_str($effective));

        foreach ($q->cursor() as $item) {
            $linkId = $item->link_id;
            unset($item->link_id);
            if (!array_key_exists($linkId, $ret)) $ret[$linkId] = [];
            $ret[$linkId][] = $item;
        }
        return $ret;
    }

}
