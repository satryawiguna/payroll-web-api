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

use App\Core\Repositories\AbstractRepository;
use App\Modules\CompensationAdmin\Models\PayrollElement;
use App\Modules\CompensationAdmin\Models\PayrollInputValue;
use App\Modules\CompensationAdmin\Models\SalaryBasis;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;

class SalaryBasisRepository extends AbstractRepository
{
    protected SalaryBasis $model;

    function __construct(SalaryBasis $model)
    {
        $this->model = $model;
    }

    function listAll($companyId): array
    {
        $ret = [];
        $q = $this->query($companyId, [
            'columns' => ['salary_basis_id', 'salary_basis_code', 'salary_basis_name', 'element_id', 'input_value_id'],
        ]);
        foreach ($q->cursor() as $item) {
            $ret[$item->salary_basis_code] = $item;
        }
        return $ret;
    }

    protected function inquiry($companyId, ?Carbon $effective, ?array $criteria, ?array $options = null): Builder
    {
        return $this
            ->query($companyId, $criteria, $options)
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
