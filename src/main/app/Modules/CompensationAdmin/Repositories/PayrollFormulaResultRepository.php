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
use App\Modules\CompensationAdmin\Models\PayrollElement;
use App\Modules\CompensationAdmin\Models\PayrollFormulaResult;
use App\Modules\CompensationAdmin\Models\PayrollInputValue;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;

class PayrollFormulaResultRepository extends AbstractTrackingRepository
{
    protected PayrollFormulaResult $model;

    function __construct(PayrollFormulaResult $model)
    {
        $this->model = $model;
    }

    function allResults($companyId, Carbon $effective): array
    {
        $q = $this->query($companyId, [
            'columns' => [
                'result_id', 'formula_id', 'result_code', 'element_id', 'input_value_id', 'iv.value_code',
                'formula_expr',
            ],
            'sorts' => ['formula_id', 'result_code']])
            ->join(PayrollInputValue::table('iv'), function(JoinClause $join) use ($effective) {
                $join->on('iv.input_value_id', '_.input_value_id');
                $join->whereRaw('? between iv.effective_first and iv.effective_last', date_to_str($effective));
            })
            ->whereRaw('? between _.effective_first and _.effective_last', date_to_str($effective));

        $ret = [];
        foreach ($q->cursor() as $item) {
            $formulaId = $item->formula_id;
            unset($item->formula_id);
            if (!array_key_exists($formulaId, $ret)) $ret[$formulaId] = [];
            $ret[$formulaId][] = $item;
        }
        return $ret;
    }

    function getPerFormula($formulaId, $companyId, Carbon $effective, ?array $options = null): array
    {
        return $this
            ->inquiry($companyId, $effective, ['sort' => ['el.processing_priority', 'iv.input_value_code']], $options)
            ->where('formula_id', $formulaId)
            ->get()->toArray();
    }

    protected function inquiry($companyId, ?Carbon $effective, ?array $criteria, ?array $options = null): Builder
    {
        return $this
            ->query($companyId, $effective, $criteria, $options)
            ->join(PayrollElement::table('el'), function(JoinClause $join) use ($effective) {
                $join->on('el.element_id', '_.element_id');
                $join->whereRaw('? between el.effective_first and el.effective_last', date_to_str($effective));
            })
            ->join(PayrollInputValue::table('iv'), function(JoinClause $join) use ($effective) {
                $join->on('iv.input_value_id', '_.input_value_id');
                $join->whereRaw('? between iv.effective_first and iv.effective_last', date_to_str($effective));
            });
    }

}
