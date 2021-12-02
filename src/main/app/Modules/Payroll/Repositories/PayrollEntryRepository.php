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
use App\Core\Repositories\AbstractTrackingRepository;
use App\Modules\CompensationAdmin\Models\PayrollElement;
use App\Modules\Payroll\Models\PayrollEntry;
use App\Modules\Payroll\Models\PayrollEntryValue;
use App\Modules\Payroll\Models\PayrollPerProcess;
use App\Modules\Payroll\Models\PayrollProcess;
use App\Modules\Payroll\Models\PayrollProcessResult;
use App\Modules\Personal\Models\EmployeeSalary;
use App\Modules\Personal\Repositories\EmployeeRepository;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class PayrollEntryRepository extends AbstractTrackingRepository
{
    protected PayrollEntry $model;
    private PayrollEntryValueRepository $valueRepo;
    private EmployeeRepository $employeeRepo;

    function __construct(PayrollEntry $model, PayrollEntryValueRepository $valueRepo, EmployeeRepository $employeeRepo)
    {
        $this->model = $model;
        $this->valueRepo = $valueRepo;
        $this->employeeRepo = $employeeRepo;
    }

    function getEmployees($companyId, Carbon $effective, ?array $criteria = null): Builder
    {
        if ($criteria == null) $criteria = [];
        $criteria['columns'] = [
            '_.id as employee_id', 'nik as employee_no', 'full_name as employee_name', 'g.name as gender_name',
            'r.name as religion_name', 'm.name as marital_status',
            DB::raw('(select count(*) from '.SB_PREFIX.'childs c where c.employee_id = _.id and c.deleted_at is null) as child_count'),
            DB::raw("case
                when ifnull(_.phone, '') != '' && ifnull(_.mobile, '') != '' then concat(_.phone, ', ', _.mobile)
                when ifnull(_.phone, '') != '' then _.phone
                else _.mobile
            end as phone_no"),
            DB::raw('cast(_.join_date as date) as join_date'),
            DB::raw('cast(x.termination_date as date) as termination_date'),

            'dep_m.work_unit_id as department_id', 'dep.title as department_name',
            'prj_m.project_id', 'prj.name as project_name', 'office_id', 'ofc.name as office_name',
            '_.work_area_id as location_id', 'loc.title as location_name', 'pos_m.position_id', 'pos.name as position_name',
            'pos_m.grade_id', 'grd.name as grade_name',

            'pay_group_id', 'pg.pay_group_name', 'people_group', 'employee_category', 'salary_basis_id', 's.basic_salary',
        ];
        return $this->employeeRepo
            ->getAll($companyId, $effective, $criteria)
            ->leftJoin(EmployeeSalary::table('s'), function(JoinClause $join) use ($effective) {
                $join->on('s.employee_id', '_.id');
                $join->whereRaw('? between s.change_date and s.date_to', date_to_str($effective));
            });
    }

    function getOne(): ?Builder
    {
        [$id, $companyId, $effective, $columns] = $this->argsGetOne(func_get_args());
        if ($columns === null) {
            $columns = [
                'entry_id', 'employee_id', 'element_id',
                '_.effective_start as p_effective_start', '_.effective_end as p_effective_end',
                'v.value_id', 'v.input_value_id', 'v.effective_start', 'v.effective_end', 'v.entry_value'
            ];
        }

        return $this->query($companyId, $effective, [
            'columns' => $columns,
            'sorts' => ['employee_id', 'element_id', 'entry_id']])
            ->leftJoin(PayrollEntryValue::table('v'), function(JoinClause $join) use ($effective) {
                $join->on('v.entry_id', '_.entry_id');
                $join->whereRaw('? between v.effective_start and v.effective_end', date_to_str($effective));
            })
            ->where('_.entry_id', $id);
    }

    function listAll(string $processType, array $employeeIds, Carbon $effective): Builder
    {
        $q = $this->query($effective, [
            'columns' => [
                'entry_id', 'employee_id', 'element_id',
                '_.effective_start as p_effective_start', '_.effective_end as p_effective_end',
                'v.value_id', 'v.input_value_id', 'v.effective_start', 'v.effective_end', 'v.entry_value'
            ],
            'sorts' => ['employee_id', 'element_id', 'entry_id']])
            ->leftJoin(PayrollEntryValue::table('v'), function(JoinClause $join) use ($effective) {
                $join->on('v.entry_id', '_.entry_id');
                $join->whereRaw('? between v.effective_start and v.effective_end', date_to_str($effective));
            })
            ->whereIn('_.employee_id', $employeeIds);

        // Retro pay hanya element yang ada element retro-nya
        if ($processType === PROCESS_TYPE_RETROPAY) {
            $q->join(PayrollElement::table('el'), function(JoinClause $join) use ($effective) {
                $join->on('el.element_id', '_.element_id');
                $join->whereRaw('? between el.effective_first and el.effective_last', date_to_str($effective));
            });
            $q->whereNotNull('el.retro_element_id');
        }
        return $q;
    }

    function insertFromRetro(object $item, Carbon $entryStart, Carbon $entryEnd, array $elements, UserPrincipal $user): ?int
    {
        $element = array_find($elements, function($d) use ($item) { return $d->element_id === $item->element_id; });
        if (empty($element->retro_element_id)) return null;

        $isNew = $item->existing_entry_id === null || $item->has_processed === 1;
        $entryId = $isNew ? generate_id() : $item->existing_entry_id;
        if ($isNew) {
            $this->insert([
                'entry_id' => $entryId,
                'company_id' => $user->company_id,
                'effective_first' => BOT,
                'effective_start' => date_to_str($entryStart),
                'effective_end' => date_to_str($entryEnd),
                'effective_last' => EOT,
                'employee_id' => $item->employee_id,
                'element_id' => $element->retro_element_id,
                'ref_retro_result_id' => $item->result_id,
                'created_by' => $user->username,
            ]);
        } else {
            $this->updateById($entryId, [
                'effective_first' => BOT,
                'effective_start' => date_to_str($entryStart),
                'effective_end' => date_to_str($entryEnd),
                'effective_last' => EOT,
                'employee_id' => $item->employee_id,
                'element_id' => $element->retro_element_id,
                'ref_retro_result_id' => $item->result_id,
                'updated_by' => $user->username,
            ]);
        }

        if (!$isNew) {
            $this->valueRepo->delete('entry_id = ?', $entryId);
        }

        $retroElement = array_find($elements, function($d) use ($element) { return $d->element_id === $element->retro_element_id; });
        if ($retroElement === null) return $entryId;

        foreach ($retroElement->values as $value) {
            $data = [
                'value_id' => generate_id(),
                'company_id' => $user->company_id,
                'effective_first' => BOT,
                'effective_start' => date_to_str($entryStart),
                'effective_end' => date_to_str($entryEnd),
                'effective_last' => EOT,
                'entry_id' => $entryId,
                'input_value_id' => $value->input_value_id,
                'value' => $value->default_value,
                'created_by' => $user->username,
                'crated_at' => now(),
            ];
            if ($value->value_code === INPUT_VALUE_PAY_VALUE) {
                $data['value'] = $item->retro_value;
            } else if ($value->value_code === INPUT_VALUE_DESCRIPTION) {
                $data['value'] = 'Retro '.$this->strPeriod($item->period_start, $item->period_end);
            }
            $this->valueRepo->insert($data);
        }

        return $entryId;
    }

    private function strPeriod($start, $end): string
    {
        if (!($start instanceof Carbon)) $start = str_to_date($start);
        if (!($end instanceof Carbon)) $end = str_to_date($end);
        $isFullMonth = $start->day === 1 && $end->isLastOfMonth();
        $inSameYear = $start->year === $end->year;
        $inSameMonth = $start->month === $end->month && $inSameYear;
        if ($isFullMonth) {
            if ($inSameYear) {
                return $end->format('F Y');
            } else {
                return $start->format('F Y').' - '.$end->format('F Y');
            }
        } else {
            if ($inSameMonth) {
                return $start->format('d').' - '.$end->format('d F Y');
            } else if ($inSameYear) {
                return $start->format('d F').' - '.$end->format('d F Y');
            } else {
                return $start->format('d F Y').' - '.$end->format('d F Y');
            }
        }
    }

    function deleteFromRetro($employeeId, Carbon $periodStart, Carbon $periodEnd)
    {
        DB::statement("
            delete from ".PayrollEntry::table()."
            where employee_id = ?
              and entry_id in (
                select r.ref_entry_id
                from ".PayrollProcessResult::table()." r
                     join ".PayrollPerProcess::table()." per on per.per_process_id = r.per_process_id
                     join ".PayrollProcess::table()." p on p.process_id = per.process_id
                where per.employee_id = ?
                  and per.period_start <= ? and per.period_end >= ?
                  and p.process_type = '".PROCESS_TYPE_RETROPAY."'
                  and per.is_validated = 0
                  and r.retro_has_processed = 0
                  and r.ref_entry_id is not null
              )",
            [$employeeId, $employeeId, date_to_str($periodEnd), date_to_str($periodStart)]
        );
    }
}
