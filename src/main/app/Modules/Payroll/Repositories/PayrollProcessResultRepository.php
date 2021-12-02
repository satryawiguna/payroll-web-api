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
use App\Modules\Payroll\Models\PayrollPerProcess;
use App\Modules\Payroll\Models\PayrollProcess;
use App\Modules\Payroll\Models\PayrollProcessResult;
use App\Modules\Payroll\Models\PayrollProcessResultValue;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollProcessResultRepository extends AbstractRepository
{
    protected PayrollProcessResult $model;
    private PayrollProcessResultValueRepository $valueRepo;

    function __construct(PayrollProcessResult $model, PayrollProcessResultValueRepository $valueRepo)
    {
        $this->model = $model;
        $this->valueRepo = $valueRepo;
    }

    function insertPerProcessResult(string $processType, $perProcessId, int $seqNo,
                                           Carbon $periodStart, Carbon $periodEnd, array $entry, UserPrincipal $user)
    {
        $resultId = $this->insert([
            'company_id' => $user->company_id,
            'per_process_id' => $perProcessId,
            'element_id' => $entry['element_id'],
            'element_code' => '--',
            'element_seq_no' => $seqNo,
            'period_start' => date_to_str($periodStart),
            'period_end' => date_to_str($periodEnd),
            'ref_entry_id' => $entry['entry_id'] ?? null,
            'retro_has_processed' => ($processType === PROCESS_TYPE_RETROPAY) ? 0 : null,
            'created_by' => $user->username,
        ]);

        if (isset($entry['values']) && !empty($entry['values'])) {
            $values = array_map(function($v) use ($resultId, $user) {
                return [
                    'company_id' => $user->company_id,
                    'result_id' => $resultId,
                    'input_value_id' => $v['input_value_id'],
                    'value_code' => '--',
                    'value' => $v['entry_value'] ?? $v['link_value'] ?? $v['default_value'] ?? null,
                    'created_by' => $user->username,
                ];
            }, $entry['values']);
            $this->valueRepo->insert($values);
        }
    }

    function insertOrUpdateProcessEntries(string $processType, object $item, array $entries, UserPrincipal $user)
    {
        foreach ($entries as $entry) {
            if (isset($entry->result_id)) {
                // update period start/end per_process_result
                $this->updateById($entry->result_id, [
                    'period_start' => $entry->period_start,
                    'period_end' => $entry->period_end,
                    'element_code' => $entry->element_code,
                ]);
            } else {
                // insert new per_process_result (case element trigger)
                $entry->result_id = $this->insert([
                    'company_id' => $user->company_id,
                    'per_process_id' => $item->per_process_id,
                    'element_id' => $entry->element_id,
                    'element_code' => $entry->element_code,
                    'element_seq_no' => $entry->element_seq_no,
                    'parent_element_id' => $entry->parent_element_id,
                    'period_start' => $entry->period_start,
                    'period_end' => $entry->period_end,
                    'retro_value' => ($processType === PROCESS_TYPE_RETROPAY) ? 0 : null,
                    'retro_has_processed' => ($processType === PROCESS_TYPE_RETROPAY) ? 0 : null,
                    'created_by' => $user->username,
                ]);
            }

            // insert atau update per_process_result_value
            foreach ($entry->values as $value) {
                if (isset($value->value_id)) {
                    if ($entry->is_valid) {
                        $this->valueRepo->updateById($value->value_id, ['value_code' => $value->value_code]);
                    } else {
                        $this->valueRepo->updateById($value->value_id, ['value_code' => $value->value_code, 'value' => null]);
                    }
                } else {
                    $value->value_id = $this->valueRepo->insert([
                        'company_id' => $user->company_id,
                        'result_id' => $entry->result_id,
                        'input_value_id' => $value->input_value_id,
                        'value_code' => $value->value_code,
                        'value' => null,
                        'created_by' => $user->username,
                    ]);
                }
            }
        }
    }

    function updateDiffRetro($perProcessId, object $item): int
    {
        return $this->model
            ->where('per_process_id', $perProcessId)
            ->where('element_id', $item->element_id)
            ->where('element_seq_no', $item->element_seq_no)
            ->update(['retro_value' => $item->diff_value]);
    }

    function clearBeforeCalculate($processId, string $processType)
    {
        // Hapus element trigger
        DB::statement("
            delete r
            from ".PayrollProcessResult::table()." r
                 join ".PayrollPerProcess::table()." per on per.per_process_id = r.per_process_id
            where per.process_id = ?
              and r.parent_element_id is not null",
            [$processId]
        );

        // Reset retro value = null
        if ($processType === PROCESS_TYPE_RETROPAY) {
            DB::statement("
                update ".PayrollProcessResult::table()." u
                join ".PayrollPerProcess::table()." per on per.per_process_id = u.per_process_id
                set u.retro_value = 0
                where per.process_id = ?",
                [$processId]
            );
        }
    }

    function getProcessEntries($perProcessId): array
    {
        $ret = [];

        $q = $this->query([
            'columns' => [
                'result_id', 'element_id', 'element_seq_no', 'ref_entry_id', 'period_start', 'period_end',
                'v.value_id', 'v.input_value_id', 'v.value_code', 'v.value'
            ],
            'sorts' => ['e.processing_priority', 'e.element_id', '_.element_seq_no', '_.result_id']])
            ->join(PayrollElement::table('e'), 'e.element_id', '_.element_id')
            ->leftJoin(PayrollProcessResultValue::table('v'), 'v.result_id', '_.result_id')
            ->where('_.per_process_id', $perProcessId);

        $lastResultId = null;
        $item = null;
        foreach ($q->get() as $d) {
            if ($lastResultId !== $d->result_id) {
                $item = (object) [
                    'result_id' => $d->result_id,
                    'element_id' => $d->element_id,
                    'element_seq_no' => $d->element_seq_no,
                    'period_start' => $d->period_start,
                    'period_end' => $d->period_end,
                    'values' => [],
                ];
                $ret[] = $item;
            }
            if ($d->value_id !== null) {
                $item->values[] = (object) [
                    'value_id' => $d->value_id,
                    'input_value_id' => $d->input_value_id,
                    'value' => $d->value,
                ];
            }
            $lastResultId = $d->result_id;
        }
        return $ret;
    }

    function hasBeenProcessed(string $processType, $employeeId, $elementId,
                                     Carbon $periodStart, Carbon $periodEnd, $excludePerProcessId): bool
    {
        $q = $this->query(['columns' => '*'])
            ->join(PayrollPerProcess::table('per'), 'per.peer_process_id', '_.per_process_id')
            ->join(PayrollProcess::table('p'), 'p.process_id', 'per.process_id')
            ->where('per.employee_id', $employeeId)
            ->where('_.element_id', $elementId)
            ->where('_.period_start', '<=', date_to_str($periodEnd))
            ->where('_.period_end', '>=', date_to_str($periodStart))
            ->where('_.per_process_id', '<>', $excludePerProcessId)
            ->where('per.process_status', PROCESS_STATUS_SUCCESS)
            ->where('p.process_type', $processType);
        return $q->exists();
    }
}
