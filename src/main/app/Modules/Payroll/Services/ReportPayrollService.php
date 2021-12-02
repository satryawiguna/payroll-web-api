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

namespace App\Modules\Payroll\Services;

use App\Core\Auth\UserPrincipal;
use App\Core\Services\BaseService;
use App\Modules\Payroll\Repositories\ReportPayrollRepository;
use Carbon\Carbon;

class ReportPayrollService extends BaseService
{
    protected ReportPayrollRepository $repo;

    function __construct(ReportPayrollRepository $repo)
    {
        $this->repo = $repo;
    }

    function perPayslip($employeeId, Carbon $periodStart, Carbon $periodEnd, UserPrincipal $user): array
    {
        $ixs = ['earnings' => [], 'deductions' => []];
        $ret = [];

        $cur = $this->repo->getPayslip($employeeId, $user->company_id, $periodStart, $periodEnd);
        foreach ($cur as $item) {
            $type = ($item->group_type === PAYSLIP_GROUP_EARNINGS) ? 'earnings' : 'deductions';
            if (!array_key_exists($type, $ret)) $ret[$type] = [];
            if (!isset($ixs[$type][$item->group_id])) {
                $ix = sizeof($ixs[$type]);
                $ixs[$type][$item->group_id] = $ix;
                $ret[$type][] = ['group_name' => $item->group_name, 'items' => []];
            } else {
                $ix = $ixs[$type][$item->group_id];
            }
            $ret[$type][$ix]['items'][] = [
                'label' => $item->label,
                'description' => $item->description,
                'division' => $item->division,
                'division_type' => $item->division_type,
                'pay_value' => +$item->pay_value,
            ];
        }
        return $ret;
    }
}
