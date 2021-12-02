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

use App\Core\Auth\UserPrincipal;
use App\Core\Repositories\AbstractRepository;
use App\Modules\Payroll\Models\PayrollPerProcess;
use App\Modules\Payroll\Models\PayrollProcess;
use App\Modules\Payroll\Models\PayrollProcessResult;
use App\Modules\Payroll\Models\PayrollProcessResultValue;
use App\Modules\Personal\Models\EmployeeSalary;
use App\Modules\Personal\Repositories\EmployeeRepository;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class PayrollPerProcessRepository extends AbstractRepository
{
    protected PayrollPerProcess $model;
    private EmployeeRepository $employeeRepo;
    private PayrollEntryRepository $entryRepo;

    function __construct(PayrollPerProcess $model, EmployeeRepository $employeeRepo, PayrollEntryRepository $entryRepo)
    {
        $this->model = $model;
        $this->employeeRepo = $employeeRepo;
        $this->entryRepo = $entryRepo;
    }

    function getEmployees($companyId, Carbon $effective, ?array $criteria): Builder
    {
        return $this->entryRepo->getEmployees($companyId, $effective, $criteria);
    }

    function iteratePerProcess($processId, Carbon $effective, UserPrincipal $user, \Closure $block)
    {
        $criteria = ['columns' => [
            'per.per_process_id', 'per.employee_id', 'per.period_start', 'per.period_end', 'per.is_validated',
            DB::raw('cast(_.join_date as date) as join_date'),
            DB::raw('cast(x.termination_date as date) as termination_date'),
            DB::raw('cast(x.termination_date as date) as last_standard_process'),
            DB::raw('cast(x.termination_date as date) as final_close'),

            'dep_m.work_unit_id as department_id', 'prj_m.project_id', 'office_id', '_.work_area_id as location_id',
            'pos_m.position_id', 'pos_m.grade_id', 'pay_group_id', 'people_group', 'employee_category',
            'salary_basis_id', 's.basic_salary',
        ]];

        $q = $this->employeeRepo
            ->getAll($user->company_id, $effective, $criteria)
            ->leftJoin(EmployeeSalary::table('s'), function(JoinClause $join) use ($effective) {
                $join->on('s.employee_id', '_.id');
                $join->whereRaw('? between s.change_date and s.date_to', date_to_str($effective));
            })
            ->join(PayrollPerProcess::table('per'), 'per.employee_id', '_.id')
            ->where('per.process_id', $processId);

        foreach ($q->cursor() as $item) {
            $block($item);
        }
    }

    function getElementValue($perProcessId, $elementId, $inputValueId, int $seqNo): ?string
    {
        $q = DB::table(PayrollProcessResult::table('r'))
            ->join(PayrollProcessResultValue::table('v'), 'v.result_id', 'r.result_id')
            ->select('v.value')
            ->where('r.per_process_id', $perProcessId)
            ->where('r.element_id', $elementId)
            ->where('r.element_seq_no', '<=', $seqNo)
            ->where('v.input_value_id', $inputValueId)
            ->orderBy('r.element_seq_no', 'desc');

        $ret = $q->first();
        return ($ret !== null) ? $ret->value : null;
    }

    function hasPayrollOnPeriod($employeeId, Carbon $period): bool
    {
        $q = $this->query(['columns' => '*'])
            ->join(PayrollProcess::table('p'), 'p.process_id', '_.process_id')
            ->where('_.employee_id', $employeeId)
            ->where('p.process_type', PROCESS_TYPE_PAYROLL)
            ->whereRaw('? between _.period_start and _.period_end', date_to_str($period));
        return $q->exists();
    }

    function insertPerProcess($processId, $employeeId, Carbon $periodStart, Carbon $periodEnd,
                                     UserPrincipal $user): int
    {
        return $this->insert([
            'company_id' => $user->company_id,
            'process_id' => $processId,
            'employee_id' => $employeeId,
            'period_start' => date_to_str($periodStart),
            'period_end' => date_to_str($periodEnd),
            'process_status' => PROCESS_STATUS_NEW,
            'created_by' => $user->username,
        ]);
    }

    function updateSalaryBasis($perProcessId, ?object $salaryBasis, ?float $basicSalary)
    {
        $this->updateById($perProcessId, [
            'salary_basis_id' => ($salaryBasis !== null) ? $salaryBasis->salary_basis_id : null,
            'basic_salary' => ($basicSalary !== null) ? round($basicSalary, 2) : null,
        ]);
    }

    /**
     * Update summary pay_per_process:
     * Update pay_value serta update boundary period_start dan period_end berdasarkan min period_start dan
     * max period_end pay_per_process_result.
     */
    function updatePerSummary($processId)
    {
        // Update periode start/end
        DB::statement("
            update ".PayrollPerProcess::table()." u
            join (select r.per_process_id, min(r.period_start) as min_period_start, max(r.period_end) as max_period_end
                  from ".PayrollProcessResult::table()." r
                       join ".PayrollPerProcess::table()." per on per.per_process_id = r.per_process_id
                  where per.process_id = ?
                  group by r.per_process_id
              ) p on p.per_process_id = u.per_process_id
            set u.period_start = p.min_period_start,
                u.period_end = p.max_period_end",
            [$processId]
        );

        // Update pay value process result ambil dari process result value
        DB::statement("
            update ".PayrollProcessResult::table()." u
            join (select v.result_id,
                  sum(case when value_code = '".INPUT_VALUE_DAY."' then cast(v.value as int) end) as day,
                  sum(case when value_code = '".INPUT_VALUE_HOUR."' then cast(v.value as float) end) as hour,
                  sum(case when value_code = '".INPUT_VALUE_RATE."' then cast(v.value as float) end) as rate,
                  sum(case when value_code = '".INPUT_VALUE_COUNT."' then cast(v.value as int) end) as count,
                  sum(case when value_code = '".INPUT_VALUE_CHILD_COUNT."' then cast(v.value as int) end) as child_count,
                  sum(case when value_code = '".INPUT_VALUE_PAY_VALUE."' then coalesce(cast(v.value as decimal(17,2)), 0.0) else 0.0 end) as pay_value,
                  max(case when value_code = '".INPUT_VALUE_DESCRIPTION."' then v.value end) as description
                  from ".PayrollProcessResult::table()." r
                       left join ".PayrollProcessResultValue::table()." v on v.result_id = r.result_id
                       join ".PayrollPerProcess::table()." per on per.per_process_id = r.per_process_id
                  where per.process_id = ?
                  group by v.result_id
              ) p on p.result_id = u.result_id
            set u.pay_value = p.pay_value,
                u.division = coalesce(p.day, p.hour, p.rate, p.count, p.child_count),
                u.division_type = case
                    when p.day is not null then '".INPUT_VALUE_DAY."'
                    when p.hour is not null then '".INPUT_VALUE_HOUR."'
                    when p.rate is not null then '".INPUT_VALUE_RATE."'
                    when p.count is not null then '".INPUT_VALUE_COUNT."'
                    when p.child_count is not null then '".INPUT_VALUE_CHILD_COUNT."'
                end,
                u.description = p.description",
            [$processId]
        );
    }

    function updateStatus($perProcessId, string $status)
    {
        $this->updateById($perProcessId, ['process_status' => $status]);
    }

    function deleteNotProcessedRetro($employeeId, Carbon $periodStart, Carbon $periodEnd)
    {
        // hapus pay_per_process_result yang belum diproses payroll
        DB::statement("
            delete r
            from ".PayrollProcessResult::table()." r
                 join ".PayrollPerProcess::table()." per on per.per_process_id = r.per_process_id
                 join ".PayrollProcess::table()." p on p.process_id = per.process_id
            where p.process_type = '".PROCESS_TYPE_RETROPAY."'
              and per.employee_id = ?
              and per.period_start <= ? and per.period_end >= ?
              and per.is_validated = 0
              and r.retro_has_processed = 0",
            [$employeeId, date_to_str($periodEnd), date_to_str($periodStart)]
        );

        // hapus pay_per_process jika result kosong
        DB::statement("
            delete per
            from ".PayrollPerProcess::table()." per
            where per.employee_id = ?
              and per.period_start <= ? and per.period_end >= ?
              and not exists(
                  select * from ".PayrollProcessResult::table()." r
                  where r.per_process_id = per.per_process_id
              )",
            [$employeeId, date_to_str($periodEnd), date_to_str($periodStart)]
        );
    }

    function deletePerProcess($processId): int
    {
        return $this->delete('process_id = ?', $processId);
    }
}
