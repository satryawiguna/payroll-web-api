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
use App\Modules\CompensationAdmin\Models\PayrollFormula;
use App\Modules\MasterData\Repositories\FormulaListRepository;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;

class PayrollFormulaRepository extends AbstractTrackingRepository
{
    protected PayrollFormula $model;
    protected PayrollFormulaResultRepository $resultRepo;
    protected FormulaListRepository $formulaListRepo;

    function __construct(PayrollFormula $model, PayrollFormulaResultRepository $resultRepo, FormulaListRepository $formulaListRepo)
    {
        $this->model = $model;
        $this->resultRepo = $resultRepo;
        $this->formulaListRepo = $formulaListRepo;
    }

    protected function inquiry($companyId, ?Carbon $effective, ?array $criteria, ?array $options = null): Builder
    {
        return $this
            ->query($companyId, $effective, $criteria, $options)
            ->leftJoin(PayrollElement::table('el'), function(JoinClause $join) use ($effective) {
                $join->on('el.element_id', '_.element_id');
                $join->whereRaw('? between el.effective_first and el.effective_last', date_to_str($effective));
            });
    }

    function allFormulasPerElement($companyId, Carbon $effective): array
    {
        $formulaList = $this->formulaListRepo->allFormulas(FORMULA_CATEGORY_PAYROLL);
        $results = $this->resultRepo->allResults($companyId, $effective);

        $q = $this->query($effective, [
            'columns' => ['formula_id', 'element_id', 'formula_type', 'formula_def'],
            'sorts' => ['element_id', 'formula_name']])
            ->whereRaw('? between _.effective_first and _.effective_last', date_to_str($effective));

        $ret = [];
        foreach ($q->cursor() as $item) {
            $sp = $formulaList[strtolower($item->formula_def)];
            $formula = (object) [
                'formula_id' => $item->formula_id,
                'formula_type' => strtoupper($item->formula_type),
                'procedure_name' => ($sp !== null) ? $sp->formula_name : null,
                'procedure_params' => ($sp !== null) ? $sp->params : null,
                'results' => $results[$item->formula_id] ?? [],
            ];
            if (!array_key_exists($item->element_id, $ret)) $ret[$item->element_id] = [];
            $ret[$item->element_id][] = $formula;
        }
        return $ret;
    }

}
