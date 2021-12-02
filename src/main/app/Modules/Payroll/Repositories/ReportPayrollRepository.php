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

namespace App\Modules\Payroll\Repositories;

use App\Modules\CompensationAdmin\Models\ElementClassification;
use App\Modules\CompensationAdmin\Models\PayrollBalance;
use App\Modules\CompensationAdmin\Models\PayrollBalanceFeed;
use App\Modules\CompensationAdmin\Models\PayrollElement;
use App\Modules\Payroll\Models\PayrollPerProcess;
use App\Modules\Payroll\Models\PayrollProcess;
use App\Modules\Payroll\Models\PayrollProcessResult;
use App\Modules\Payroll\Models\Payslip;
use App\Modules\Payroll\Models\PayslipGroup;
use Carbon\Carbon;
use Generator;
use Illuminate\Support\Facades\DB;

class ReportPayrollRepository
{

    function getPayslip($employeeId, $companyId, Carbon $periodStart, Carbon $periodEnd): Generator
    {
        $sStart = date_to_str($periodStart);
        $sEnd = date_to_str($periodEnd);
        $sEffective = $sEnd;

        return DB::cursor("
            select ps.payslip_id, coalesce(ps.label, el.element_name, bl.balance_name) as label,
                   ps.group_id, g.group_name, g.group_type,
                   r1.description, r1.division, r1.division_type, coalesce(r1.pay_value, r2.pay_value) as pay_value
            from ".Payslip::table()." ps
                 join ".PayslipGroup::table()." g on g.group_id = ps.group_id
                 left join ".PayrollElement::table()." el on el.element_id = ps.element_id and ? between el.effective_first and el.effective_last
                 left join ".PayrollBalance::table()." bl on bl.balance_id = ps.balance_id
                 left join (
                     select r.element_id, r.description, r.division, r.division_type, r.pay_value, per.is_validated
                     from ".PayrollProcess::table()." p
                          join ".PayrollPerProcess::table()." per on per.process_id = p.process_id
                          join ".PayrollProcessResult::table()." r on r.per_process_id = per.per_process_id
                          join ".Payslip::table()." ps on ps.element_id = r.element_id
                     where p.process_type = 'P'
                       and p.period_start <= ? and p.period_end >= ?
                       and per.is_validated = 1
                       and per.employee_id = ?
                   ) r1 on r1.element_id = ps.element_id
                left join (
                    select b.balance_id, sum(r.pay_value) as pay_value
                    from ".PayrollProcess::table()." p
                         join ".PayrollPerProcess::table()." per on per.process_id = p.process_id
                         join ".PayrollProcessResult::table()." r on r.per_process_id = per.per_process_id
                         join (
                             select f.balance_id, coalesce(f.element_id, el.element_id) as element_id
                             from ".PayrollBalanceFeed::table()." f
                                  join ".Payslip::table()." ps on ps.balance_id = f.balance_id
                                  left join ".ElementClassification::table()." cl on cl.classification_id = f.classification_id
                                  left join ".PayrollElement::table()." el on el.classification_id = cl.classification_id and ? between el.effective_first and el.effective_last
                             where (f.company_id is null or f.company_id = ?)
                               and ? between f.effective_first and f.effective_last
                          ) b on b.element_id = r.element_id
                    where p.process_type = 'P'
                      and p.period_start <= ? and p.period_end >= ?
                      and per.is_validated = 1
                      and per.employee_id = ?
                    group by b.balance_id
                  ) r2 on r2.balance_id = ps.balance_id
            where (ps.company_id is null or ps.company_id = ?)
               and (ps.hide_when_empty = 0 or
                   ((r1.pay_value is not null and r1.pay_value <> 0) or (r2.pay_value is not null and r2.pay_value <> 0)))
            order by ps.seq_no",
            [$sEffective, $sEnd, $sStart, $employeeId,
             $sEffective, $companyId, $sEffective, $sEnd, $sStart, $employeeId,
             $employeeId]
        );
    }
}
