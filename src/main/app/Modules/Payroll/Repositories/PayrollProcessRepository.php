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
use App\Modules\CompensationAdmin\Models\PayrollElement;
use App\Modules\Payroll\Models\PayrollEntry;
use App\Modules\Payroll\Models\PayrollPerProcess;
use App\Modules\Payroll\Models\PayrollProcess;
use App\Modules\Payroll\Models\PayrollProcessResult;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollProcessRepository extends AbstractRepository
{
    protected PayrollProcess $model;

    function __construct(PayrollProcess $model)
    {
        $this->model = $model;
    }

    function insertPayProcess(string $processType, array $data, UserPrincipal $user): int
    {
        $filters = [];
        isset($data['office_id']) && $filters['office_id'] = $data['office_id'];
        isset($data['location_id']) && $filters['location_id'] = $data['location_id'];
        isset($data['department_id']) && $filters['department_id'] = $data['department_id'];
        isset($data['project_id']) && $filters['project_id'] = $data['project_id'];
        isset($data['position_id']) && $filters['position_id'] = $data['position_id'];
        isset($data['grade_id']) && $filters['grade_id'] = $data['grade_id'];
        isset($data['pay_group_id']) && $filters['pay_group_id'] = $data['pay_group_id'];
        isset($data['people_group']) && $filters['people_group'] = $data['people_group'];
        isset($data['employee_category']) && $filters['employee_category'] = $data['employee_category'];
        isset($data['employee_id']) && $filters['employee_id'] = $data['employee_id'];

        return $this->insert([
            'company_id' => $user->company_id,
            'process_type' => $processType,
            'batch_name' => $data['batch_name'],
            'process_date' => $data['process_date'],
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
            'ret_entry_period_start' => $data['ret_entry_period_start'] ?? null,
            'ret_entry_period_end' => $data['ret_entry_period_end'] ?? null,
            'description' => $data['description'] ?? null,
            'filter_info' => json_encode($filters),
            'created_by' => $user->username,
        ]);
    }

    function deleteRetroWhenEmpty($companyId, Carbon $periodStart, Carbon $periodEnd)
    {
        DB::statement("
            delete p
            from ".PayrollProcess::table()." p
            where p.company_id = ?
              and p.process_type = '".PROCESS_TYPE_RETROPAY."'
              and p.period_start <= ? and p.period_end >= ?
              and not exists(
                select * from ".PayrollPerProcess::table()." per
                where per.process_id = p.process_id
              )",
            [$companyId, date_to_str($periodEnd), date_to_str($periodStart)]
        );
    }

    function selectDiffRetro($processId, object $process, \Closure $block)
    {
        $periodStart = str_to_date($process->period_start);
        $periodEnd = str_to_date($process->period_end);
        $entryStart = str_to_date($process->ret_entry_period_start);
        $entryEnd = str_to_date($process->ret_entry_period_end);

        iterate_each_month($periodStart, $periodEnd, function($start, $end) use ($processId, $block, $entryStart, $entryEnd) {
            $sStart = date_to_str($start);
            $sEnd = date_to_str($end);

            $cur = DB::cursor("
                select r.per_process_id, r.employee_id, r.element_id, r.element_seq_no, r.retro_element_id,
                       r.period_start, r.period_end,
                       r.value as result_value, r.value - ex.value as diff_value
                from (
                    select per.per_process_id, per.employee_id, r.element_id, r.element_seq_no, el.retro_element_id,
                           r.period_start, r.period_end, r.pay_value as value
                    from ".PayrollProcessResult::table()." r
                         join ".PayrollPerProcess::table()." per on per.per_process_id = r.per_process_id
                         join ".PayrollElement::table()." el on el.element_id = r.element_id
                                                        and r.period_end between el.effective_first and el.effective_last
                    where per.process_id = ?
                      and per.process_status = '".PROCESS_STATUS_SUCCESS."'
                      and r.period_start <= ? and r.period_end >= ?
                      and el.retro_element_id is not null
                  ) r
                  join (
                    select per.employee_id, r.element_id, r.element_seq_no,
                           sum(if(p.process_type = '".PROCESS_TYPE_PAYROLL."', r.pay_value, r.retro_value)) as value
                    from ".PayrollProcessResult::table()." r
                         join ".PayrollPerProcess::table()." per on per.per_process_id = r.per_process_id
                         join ".PayrollProcess::table()." p on p.process_id = per.process_id
                    where per.process_id <> ?
                      and r.period_start <= ? and r.period_end >= ?
                      and ((p.process_type = '".PROCESS_TYPE_PAYROLL."') or
                           (p.process_type = '".PROCESS_TYPE_RETROPAY."' and r.retro_has_processed = 1))
                      and per.process_status = '".PROCESS_STATUS_SUCCESS."'
                      and per.is_validated = 1
                    group by per.employee_id, r.element_id, r.element_seq_no
                  ) ex on ex.employee_id = r.employee_id
                      and ex.element_id = r.element_id
                      and ex.element_seq_no = r.element_seq_no
                where r.value <> ex.value
                order by r.employee_id, r.element_id, r.element_seq_no",
                [$processId, $sEnd, $sStart, $processId, $sEnd, $sStart]
            );
            foreach ($cur as $item) {
                $block($item->per_process_id, $entryStart, $entryEnd, $item);
            }
        });
    }

    function selectRetroToEntry($processId, \Closure $block)
    {
        $q = DB::table(PayrollPerProcess::table('per'))
            ->join(PayrollProcessResult::table('r'), 'r.per_process_id', 'per.per_process_id')
            ->leftJoin(PayrollEntry::table('e'), 'e.ref_retro_result_id', 'r.result_id')
            ->leftJoin(PayrollProcessResult::table('rr'), 'rr.ref_entry_id', 'e.entry_id')
            ->select(
                'r.result_id', 'per.employee_id', 'r.element_id', 'e.entry_id as existing_entry_id',
                'per.period_start', 'per.period_end', 'r.retro_value',
                DB::raw('case when rr.result_id is not null then 1 else 0 end as has_processed')
            )
            ->where('per.process_id', $processId)
            ->where('per.process_status', PROCESS_STATUS_SUCCESS)
            ->where('r.retro_value', '!=', 0);

        foreach ($q->cursor() as $item) {
            $block($item);
        }
    }

    function getProcess($processId, $companyId): ?object
    {
        $q = $this->query($companyId, [
            'columns' => [
                'company_id', 'process_type', 'process_date',
                'period_start', 'period_end', 'ret_entry_period_start', 'ret_entry_period_end'
            ]])
            ->where('process_id', $processId);

        return $q->first();
    }

    function setValidated($processId, bool $validated)
    {
        $this->updateById($processId, ['is_validated' => $validated ? 1 : 0]);

        DB::table(PayrollPerProcess::table())
            ->where('process_id', $processId)
            ->where('process_status', PROCESS_STATUS_SUCCESS)
            ->update(['is_validated' => $validated ? 1 : 0]);
    }

    function updateRetroHasProcessed($processId)
    {
        DB::statement("
            update ".PayrollProcessResult::table()."
            set retro_has_processed = 1
            where result_id in (
                  select ee.ref_retro_result_id
                  from ".PayrollPerProcess::table()." per
                       join ".PayrollProcessResult::table()." r on r.per_process_id = per.per_process_id
                       join ".PayrollEntry::table()." ee on ee.entry_id = r.ref_entry_id -- tidak perlu filter effective
                  where per.process_id = ?
                    and per.process_status = '".PROCESS_STATUS_SUCCESS."'
                    and r.ref_entry_id is not null
                    and ee.ref_retro_result_id is not null
              )
              and retro_has_processed = 0",
            [$processId]
        );
    }

}
