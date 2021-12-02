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
use App\Core\Services\AbstractService;
use App\Exceptions\ValidationException;
use App\Modules\CompensationAdmin\Services\PayrollElementService;
use App\Modules\CompensationAdmin\Services\SalaryBasisService;
use App\Modules\Payroll\Repositories\PayrollPerProcessRepository;
use App\Modules\Payroll\Repositories\PayrollProcessLogRepository;
use App\Modules\Payroll\Repositories\PayrollProcessRepository;
use App\Modules\Payroll\Repositories\PayrollProcessResultRepository;
use App\Modules\Payroll\Repositories\PayrollProcessResultValueRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollProcessService extends AbstractService
{
    protected PayrollProcessRepository $repo;
    private PayrollPerProcessRepository $perProcessRepo;
    private PayrollProcessResultRepository $resultRepo;
    private PayrollProcessResultValueRepository $valueRepo;
    private PayrollProcessLogRepository $logRepo;

    private SalaryBasisService $salaryBasisSvc;
    private PayrollElementService $elementSvc;
    private PayrollEntryService $entrySvc;

    function __construct(PayrollProcessRepository $repo, PayrollPerProcessRepository $perProcessRepo,
                         PayrollProcessResultRepository $resultRepo, PayrollProcessResultValueRepository $valueRepo,
                         PayrollProcessLogRepository $logRepo,
                         SalaryBasisService $salaryBasisSvc, PayrollElementService $elementSvc, PayrollEntryService $entrySvc)
    {
        $this->repo = $repo;
        $this->perProcessRepo = $perProcessRepo;
        $this->resultRepo = $resultRepo;
        $this->valueRepo = $valueRepo;
        $this->logRepo = $logRepo;

        $this->salaryBasisSvc = $salaryBasisSvc;
        $this->elementSvc = $elementSvc;
        $this->entrySvc = $entrySvc;
    }

    // <editor-fold desc="# Insert New Process">

    /**
     * Insert new raw data payroll process/retro-pay (kalkulasi dilakukan di proses lain).
     * Untuk proses retro-pay, data akan di-insert per bulan sebanyak jumlah bulan dari period_start dan period_end.
     */
    function insertNewProcess(string $processType, array $data, UserPrincipal $user): int
    {
        return DB::transaction(function() use ($processType, $data, $user) {
            // insert pay_process
            $processId = $this->repo->insertPayProcess($processType, $data, $user);

            $this->eachPerPeriod($processType, $data, function(array $per, Carbon $start, Carbon $end)
                    use ($processId, $processType, $user) {
                $employeeId = $per['employee_id'];

                // Skip jika retropay dan belum ada proses payroll di periode ini
                if ($processType === PROCESS_TYPE_RETROPAY
                    && !$this->perProcessRepo->hasPayrollOnPeriod($employeeId, $start)) {
                    return;
                }

                // insert pay_per_process
                $perProcessId = $this->perProcessRepo->insertPerProcess($processId, $employeeId, $start, $end, $user);

                $lastElementId = null;
                $seqNo = 1;
                foreach ($per['entries'] as $entry) {
                    if ($lastElementId === $entry['element_id']) {
                        $seqNo++;
                    }
                    $lastElementId = $entry['element_id'];

                    // insert pay_per_process_result, pay_per_process_result_value
                    $this->resultRepo->insertPerProcessResult($processType, $perProcessId, $seqNo, $start, $end, $entry, $user);
                }
            });

            return $processId;
        });
    }

    private function eachPerPeriod(string $processType, array $data, \Closure $block)
    {
        $periodStart = str_to_date($data['period_start']);
        $periodEnd = str_to_date($data['period_end']);

        foreach ($data['items'] as $per) {
            if ($processType === PROCESS_TYPE_RETROPAY) {
                iterate_each_month($periodStart, $periodEnd, function($start, $end) use ($per, $block) {
                    $block($per, $start, $end);
                });
            } else {
                $block($per, $periodStart, $periodEnd);
            }
        }
    }

    // </editor-fold>

    // <editor-fold desc="# Calculate Payroll">

    /**
     * Proses kalkulasi payroll/retro-pay.
     */
    function calculatePayroll($processId, UserPrincipal $user)
    {
        $process = $this->repo->getProcess($processId, $user->company_id);
        if ($process === null) abort(404, "Payroll process not found");
        $this->validateCompany($process, $user);

        $effective = $process->period_end;
        $allSalaryBasis = $this->salaryBasisSvc->listAll($user);
        $logs = [];

        DB::transaction(function() use ($processId, $effective, $process, $allSalaryBasis, $user, &$logs) {
            $cachedElements = [];
            $this->perProcessRepo->iteratePerProcess($processId, $effective, $user, function($item)
                    use ($processId, $process, $allSalaryBasis, &$cachedElements, $user, &$logs) {
                if ($item->is_validated) {
                    Log::warning("Process perProcessId: $item->per_process_id has been validated");
                    return;
                }
                Log::info("Processing perProcessId: $item->per_process_id");

                $this->logRepo->clearLog($processId, $item->per_process_id);

                $effective = str_to_date($item->period_end);
                if (!isset($cachedElements[$item->period_end])) {
                    $allElements = $this->elementSvc->listForProcess($effective, $user);
                    $cachedElements[$item->period_end] = $allElements;
                } else {
                    $allElements = $cachedElements[$item->period_end];
                }

                try {
                    // pre process: populate trigger, fill incomplete data
                    $entries = $this->preProcessEntries(
                        $processId, $process->process_type, $item, $allSalaryBasis, $allElements, $user, $logs
                    );

                    // process calculate entry, call stored procedure
                    $this->calculateEntries($processId, $effective, $item, $entries, $allElements, $user);

                    // process status
                    $status = PROCESS_STATUS_SUCCESS;
                    foreach ($entries as $entry) {
                        if (!$entry->is_valid) {
                            $status = PROCESS_STATUS_WARNING;
                            break;
                        }
                    }
                    $this->perProcessRepo->updateStatus($item->per_process_id, $status);

                } catch (\Exception $e) {
                    $this->perProcessRepo->updateStatus($item->per_process_id, PROCESS_STATUS_FAILED);
                    $this->logException($logs, $processId, $item->per_process_id, $e, $user);
                }
            });

            // Update periode start/end dan pay value process result
            $this->perProcessRepo->updatePerSummary($processId);

            if ($process->process_type === PROCESS_TYPE_RETROPAY) {
                $this->processDiffRetro($processId, $process);
            }
        });

        if (!empty($logs)) $this->logRepo->insert($logs);
    }

    private function preProcessEntries($processId, string $processType, object $item, array $allSalaryBasis, array $elements,
                                       UserPrincipal $user, array &$logs): array
    {
        $this->resultRepo->clearBeforeCalculate($processId, $processType);

        // fill element dan trigger
        $entries = $this->getEntriesToProcess($processId, $processType, $item, $elements, $user, $logs);
        $this->resultRepo->insertOrUpdateProcessEntries($processType, $item, $entries, $user);

        // Set salary basis value
        $sb = $this->getSalaryBasis($item, $allSalaryBasis);
        $basicSalary = ($sb !== null) ? $this->getBasicSalary($sb, $entries) : null;
        $this->perProcessRepo->updateSalaryBasis($item->per_process_id, $sb, $basicSalary);

        return $entries;
    }

    private function getSalaryBasis(object $item, array $allSalaryBasis): ?object
    {
        if ($item->salary_basis_id === null || empty($item->basic_salary)) return null;
        return array_find($allSalaryBasis, function($d) use ($item) {
            return $d->salary_basis_id === $item->salary_basis_id;
        });
    }

    private function getBasicSalary(object $salaryBasis, array $entries): ?float
    {
        $entry = array_find($entries, function($e) use ($salaryBasis) {
            return $e->element_id === $salaryBasis->element_id;
        });
        if ($entry === null) return null;

        $value = array_find($entry->values, function($v) use ($salaryBasis) {
            return $v->input_value_id === $salaryBasis->input_value_id;
        });
        return ($value !== null && !empty($value->entry_value)) ? +$value->entry_value : null;
    }

    private function getEntriesToProcess($processId, string $processType, object $item, array $elements,
                                         UserPrincipal $user, array &$logs): array
    {
        $ret = [];
        $entries = $this->resultRepo->getProcessEntries($item->per_process_id);
        foreach ($entries as $entry) {
            $this->addValidElement($ret, $processId, $processType, $item, $entry, $elements, $user, $logs);
        }
        return $ret;
    }

    private function addValidElement(array &$ret, $processId, string $processType, object $item, object $entry,
                                     array $elements, UserPrincipal $user, array &$logs)
    {
        if (!array_key_exists($entry->element_id, $elements)) return;
        $element = $elements[$entry->element_id];

        // update period start/end berdasarkan join_date dan termination_date
        [$periodStart, $periodEnd] = $this->getPerProcessPeriod($item, $element);
        $entry->period_start = date_to_str($periodStart);
        $entry->period_end = date_to_str($periodEnd);
        $entry->element_code = $element->element_code;

        // validate
        $entry->is_valid = true;
        if ($element->is_once_per_period
            && $this->resultRepo->hasBeenProcessed($processType, $item->employee_id, $entry->element_id,
                                                   $periodStart, $periodEnd, $item->per_process_id)) {
            $data = [
                'element_id' => $entry->element_id,
                'key' => 'payroll:error.run-once',
                'description' => 'Already processed in another payroll period',
                'severity' => SEVERITY_WARNING,
            ];
            $logs[] = $this->logInvalid($processId, $item->per_process_id, $data, $user);
            $entry->is_valid = false;
        }

        $values = [];
        foreach ($element->values as $iv) {
            $value = array_find($entry->values, function($v) use ($iv) {
                return $v->input_value_id === $iv->input_value_id;
            });
            if ($value !== null) {
                $value->value_code = $iv->value_code;
            } else {
                $value = (object) [
                    'value_id' => null,
                    'input_value_id' => $iv->input_value_id,
                    'value_code' => $iv->value_code,
                    'value' => $iv->default_value,
                ];
            }
            $values[] = $value;
        }
        $entry->values = $values;

        $ret[] = $entry;
        if (!$entry->is_valid) return;

        // next element by triggers
        $triggers = $this->getElementTriggers($entry, $elements, $element->triggers);
        foreach ($triggers as $trigger) {
            if ($trigger->element_id === $entry->element_id) continue;
            if ($this->isElementExists($ret, $trigger->element_id, $entry->element_seq_no)) continue;

            // Add valida element (recursive)
            $trigger->parent_element_id = $entry->element_id;
            $this->addValidElement($ret, $processId, $processType, $item, $trigger, $elements, $user, $logs);
        }
    }

    private function getPerProcessPeriod(object $item, object $element): array
    {
        $start = max_date($item->period_start, $item->join_date);
        $end = str_to_date($item->period_end);

        switch ($element->last_entry_type) {
            case LAST_ENTRY_TERMINATION:
                if ($item->termination_date !== null) {
                    $end = min_date($item->period_end, $item->termination_date);
                }
                break;
            case LAST_ENTRY_STANDARD_PROCESS:
                if ($item->last_standard_process !== null) {
                    $end = min_date($item->period_end, $item->last_standard_process);
                }
                break;
            case LAST_ENTRY_FINAL_CLOSE:
                if ($item->final_close !== null) {
                    $end = min_date($item->period_end, $item->final_close);
                }
                break;
        }
        return [$start, $end];
    }

    private function getElementTriggers(object $entry, array $elements, array $triggerElementIds): array
    {
        $ret = [];
        foreach ($triggerElementIds as $elementId) {
            if (!array_key_exists($elementId, $elements)) continue;

            $trigger = (object) [
                'element_id' => $elementId,
                'element_seq_no' => $entry->element_seq_no,
                'period_start' => $entry->period_start,
                'period_end' => $entry->period_end,
                'values' => array_map(function($v) { return clone $v; }, $elements[$elementId]->values),
            ];
            $ret[] = $trigger;
        }
        return $ret;
    }

    private function isElementExists(array $existing, $elementId, int $seqNo): bool
    {
        foreach ($existing as $item) {
            if ($item->element_id === $elementId && $item->element_seq_no === $seqNo) {
                return true;
            }
        }
        return false;
    }

    /**
     * @throw PayProcessException
     */
    private function calculateEntries($processId, Carbon $effective, object $item, array $entries, array $elements,
                                      UserPrincipal $user)
    {
        foreach ($entries as $entry) {
            $element = $elements[$entry->element_id];
            if (empty($element->formulas) || !$entry->is_valid) continue;

            try {
                $results = $this->calculateFormula($processId, $effective, $element->formulas, $item, $entry, $elements, $user);
                if (empty($results)) continue;

                foreach ($results as $result) {
                    if ($result->element_id === $entry->element_id && $result->element_seq_no === $entry->element_seq_no) {
                        $retEntry = $entry;
                    } else {
                        $retEntry = array_find($entries, function($e) use ($result) {
                            return $e->element_id === $result->element_id && $e->element_seq_no === $result->element_seq_no;
                        });
                        if ($retEntry === null) {
                            $msg = "Can't get entry for element_id $result->element_id";
                            abort(404, $msg, 'calculate-error');
                        }
                    }

                    $retValue = array_find($retEntry->values, function($v) use ($result) {
                        return $v->input_value_id === $result->input_value_id;
                    });
                    if ($retValue === null) {
                        $msg = "Can't get entry value for element_id $result->element_id input_value_id $result->input_value_id";
                        abort(404, $msg, 'calculate-error');
                    }

                    $retValue->value = $result->value;

                    // update pay_per_process_result_value berdasarkan formula result
                    $this->valueRepo->updateById($retValue->value_id, ['value' => $result->value]);
                }
            } catch (\Exception $e) {
                if ($e instanceof ValidationException) {
                    $e->addVariable('element_id', $entry->element_id);
                    throw $e;
                } else {
                    abort(500, $e->getMessage(), 'calculate-error', ['element_id' => $entry->element_id], $e);
                }
            }
        }
    }

    private function calculateFormula($processId, Carbon $effective, array $formulas, object $item, object $entry,
                                      array $elements, UserPrincipal $user): ?array
    {
        try {
            $ret = [];
            foreach ($formulas as $formula) {
                if ($formula->formula_type === FORMULA_TYPE_STORED_PROCEDURE) {
                    $calc = $this->calculateSpFormula($processId, $effective, $formula, $item, $entry, $user);
                    array_push($ret, $calc);
                } else if ($formula->formula_type === FORMULA_TYPE_SIMPLE_FORMULA) {
                    $calc = $this->calculateFxFormula($formula, $item, $entry->element_seq_no, $elements);
                    array_push($ret, $calc);
                }
            }
            return $ret;
        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                throw $e;
            } else {
                abort(500, $e->getMessage(), 'formula-error', null, $e);
            }
        }
    }

    private function calculateSpFormula($processId, Carbon $effective, object $formula, object $item, object $entry,
                                        UserPrincipal $user): array
    {
        $ret = [];
        $paramValues = [];
        foreach ($entry->values as $v) {
            $paramValues[strtolower($v->value_code)] = $v->value;
        }
        $paramValues['process_id'] = $processId;
        $paramValues['effective'] = date_to_str($effective);
        $paramValues['per_process_id'] = $item->per_process_id;
        $paramValues['result_id'] = $entry->result_id;
        $paramValues['element_id'] = $entry->element_id;
        $paramValues['period_start'] = date_to_str($entry->period_start);
        $paramValues['period_end'] = date_to_str($entry->period_end);
        $paramValues['company_id'] = $user->company_id;
        $paramValues['username'] = $user->username;

        $out = exec_procedure($formula->procedure_name, $formula->procedure_params, $paramValues);

        foreach ($formula->results as $result) {
            $resultName = strtolower($result->result_code);
            $ret[] = (object) [
                'element_id' => $result->element_id,
                'element_seq_no' => $entry->element_seq_no,
                'input_value_id' => $result->input_value_id,
                'value' => property_exists($out, $resultName) ? $out->{$resultName} : ($paramValues[$resultName] ?? null),
            ];
        }
        return $ret;
    }

    private function calculateFxFormula(object $formula, object $item, int $seqNo, array $elements): array
    {
        $ret = [];

        foreach ($formula->results as $result) {
            if (empty($result->formula_expr)) continue;

            $expr = $result->formula_expr;
            $vars = extract_formula_expr($expr);

            foreach ($vars as $var) {
                $el = array_find($elements, function($d) use ($var) {
                    return strtolower($d->element_code) === $var->element;
                });
                if ($el === null) continue;
                $iv = array_find($el->values, function($d) use ($var) {
                    return strtolower($d->value_code) === $var->input_value;
                });
                if ($iv === null) continue;


                $value = $this->perProcessRepo->getElementValue($item->per_process_id, $el->element_id,
                                                                $iv->input_value_id, $seqNo);
                $expr = str_ireplace($var->var, $value, $expr);
            }

            $value = null;
            try {
                $value = parse_formula($expr);
            } catch (\Exception $e) {
                $err = ['severity' => SEVERITY_WARNING];
                abort(500, "Can't parse $expr", 'formula-error', $err, $e);
            }

            $ret[] = (object) [
                'element_id' => $result->element_id,
                'element_seq_no' => $seqNo,
                'input_value_id' => $result->input_value_id,
                'value' => $value,
            ];
        }
        return $ret;
    }

    private function processDiffRetro($processId, object $process)
    {
        $this->repo->selectDiffRetro($processId, $process,
            function($perProcessId, Carbon $entryStart, $entryEnd, object $item) {
                // insert new diff retro ke pay_per_process_result
                $this->resultRepo->updateDiffRetro($perProcessId, $item);
            }
        );
    }

    // </editor-fold>

    // <editor-fold desc="# Validate Process">

    function validateProcessed($processId, UserPrincipal $user)
    {
        $process = $this->repo->getProcess($processId, $user->company_id);
        if ($process === null) abort(404, "Payroll process not found");
        $this->validateCompany($process, $user);

        DB::transaction(function() use ($processId, $process, $user) {
            // Update is_validated
            $this->repo->setValidated($processId, true);

            if ($process->process_type === PROCESS_TYPE_PAYROLL) {
                // (payroll) Update flag proses retropay dari element entry menjadi sudah diproses
                $this->repo->updateRetroHasProcessed($processId);
            }

            if ($process->process_type === PROCESS_TYPE_RETROPAY) {
                // (retro) Insert ke element entry
                $cachedElements = [];
                $this->repo->selectRetroToEntry($processId, function($item) use (&$cachedElements, $process, $user) {
                    if (!isset($cachedElements[$item->period_end])) {
                        $allElements = $this->elementSvc->listAll(str_to_date($item->period_end), $user);
                        $cachedElements[$item->period_end] = $allElements;
                    } else {
                        $allElements = $cachedElements[$item->period_end];
                    }
                    $entryStart = str_to_date($process->ret_entry_period_start);
                    $entryEnd = str_to_date($process->ret_entry_period_end);
                    $this->entrySvc->insertFromRetro($item, $entryStart, $entryEnd, $allElements, $user);
                });
            }
        });
    }

    // </editor-fold>

    // <editor-fold desc="# Delete Payroll">

    function delete($id, UserPrincipal $user): object
    {
        $process = $this->repo->getProcess($id, $user->company_id);
        if ($process === null) abort(404, "Payroll process not found");
        $this->validateCompany($process, $user);

        return DB::transaction(function() use ($id, $process, $user) {
            $start = str_to_date($process->period_start);
            $end = str_to_date($process->period_end);

            // Hapus retro yang belum diproses
            $this->deleteNotProcessedRetro($id, $start, $end, $user);

            // Hapus payroll
            $this->perProcessRepo->deletePerProcess($id);
            $count = $this->repo->deleteById($id);
            return (object) ['count' => $count];
        });
    }

    private function deleteNotProcessedRetro($processId, Carbon $periodStart, Carbon $periodEnd, UserPrincipal $user)
    {
        $this->perProcessRepo->iteratePerProcess($processId, $user, function($item) use ($user) {
            $start = str_to_date($item->period_start);
            $end = str_to_date($item->period_end);

            // Hapus element entry yang berasal dari retro
            $this->entrySvc->deleteFromRetro($item->employee_id, $start, $end);

            // Hapus existing kalkulasi payroll retro yang belum diproses payroll
            $this->perProcessRepo->deleteNotProcessedRetro($user->company_id, $start, $end);
        });

        // cleanup: hapus pay_process (retro) jika result kosong
        $this->repo->deleteRetroWhenEmpty($user->company_id, $periodStart, $periodEnd);
    }

    // </editor-fold>

    // <editor-fold desc="# Log">

    private function logException(array &$logs, $processId, $perProcessId, \Exception $e, UserPrincipal $user)
    {
        $elementId = null;
        $key = null;
        $severity = SEVERITY_ERROR;
        if ($e instanceof ValidationException) {
            $elementId = $e->getVariable('element_id') ?? $elementId;
            $key = $e->key ?? $key;
            $severity = $e->getVariable('severity') ?? $severity;
        }
        $logs[] = $this->logInvalid($processId, $perProcessId, [
            'element_id' => $elementId,
            'key' => $key,
            'description' => $this->getExceptionDescription($e),
            'severity' => $severity,
            'exception_info' => $this->getExceptionTrace($e),
        ], $user);
    }

    private function logInvalid($processId, $perProcessId, array $data, UserPrincipal $user): array
    {
        return [
            'company_id' => $user->company_id,
            'process_id' => $processId,
            'per_process_id' => $perProcessId,
            'element_id' => $data['element_id'] ?? null,
            'key' => $data['key'] ?? null,
            'description' => $data['description'] ?? null,
            'severity' => $data['severity'] ?? null,
            'exception_info' => $data['exception_info'] ?? null,
            'created_by' => $user->username,
        ];
    }

    private function getExceptionDescription(\Exception $e): ?string {
        $msg = $e->getMessage();
        if (empty($msg) || strlen($msg) <= 300) return $msg;
        return substr($msg, 0, 297).'...';
    }

    private function getExceptionTrace(\Exception $e): ?string {
        $ret = '';
        $traces = simple_trace($e);
        foreach ($traces as $i => $line) {
            if ($i > 0) $ret .= "\n";
            $ret .= $line;
        }
        if (strlen($ret) > 65535) $ret = substr($ret, 0, 65532).'...';
        return $ret;
    }

    // </editor-fold>
}
