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
use App\Core\Services\AbstractTrackingService;
use App\Modules\CompensationAdmin\Services\ElementLinkService;
use App\Modules\CompensationAdmin\Services\PayrollElementService;
use App\Modules\CompensationAdmin\Services\SalaryBasisService;
use App\Modules\Payroll\Repositories\PayrollEntryRepository;
use Carbon\Carbon;

class PayrollEntryService extends AbstractTrackingService
{
    protected PayrollEntryRepository $repo;

    private SalaryBasisService $salaryBasisSvc;
    private PayrollElementService $elementSvc;
    private ElementLinkService $linkSvc;
    private PayrollEntryValueService $valueSvc;

    function __construct(PayrollEntryRepository $repo, SalaryBasisService $salaryBasisSvc, PayrollElementService $elementSvc,
                         ElementLinkService $linkSvc, PayrollEntryValueService $valueSvc)
    {
        $this->repo = $repo;
        $this->salaryBasisSvc = $salaryBasisSvc;
        $this->elementSvc = $elementSvc;
        $this->linkSvc = $linkSvc;
        $this->valueSvc = $valueSvc;
    }

    function getEmployees(Carbon $effective, ?array $criteria, UserPrincipal $user, ?array $options = null): array
    {
        $processType = $options['process-type'] ?? PROCESS_TYPE_PAYROLL;
        $includeEntries = $options['include-entries'] ?? true;

        $q = $this->repo->getEmployees($user->company_id, $effective, $criteria);
        $p = $this->paginate($q, $criteria['per_page'] ?? null);
        if (!$includeEntries) return $p;

        $allElements = $this->elementSvc->listAll($effective, $user, $processType === PROCESS_TYPE_RETROPAY);
        $employeeIds = map($p['rows'], function($d) { return $d->employee_id; });
        $allEntries = $this->listAll($processType, $employeeIds, $effective);
        $allLinks = $this->linkSvc->listAll($effective, $user);
        $allSalaryBasis = $this->salaryBasisSvc->listAll($user);
        $elements = [];

        foreach ($p['rows'] as $i => $employee) {
            $entries = $this->getEntries(
                $employee, $allElements, $allLinks, $allEntries[$employee->employee_id] ?? [], $allSalaryBasis
            );
            $retEntries = [];
            foreach ($entries as $entry) {
                $retEntries[] = (object)[
                    'element_id' => $entry->element_id,
                    'entry_id' => $entry->entry_id,
                    'values' => array_map(function($v) { return (object)[
                        'input_value_id' => $v->input_value_id,
                        'data_type' => $v->data_type,
                        'default_value' => $v->default_value,
                        'link_value' => $v->link_value,
                        'entry_value' => $v->entry_value,
                    ];}, $entry->values),
                ];
                if (!array_key_exists($entry->element_id, $elements)) {
                    $elements[$entry->element_id] = (object) [
                        'element_id' => $entry->element_id,
                        'element_code' => $entry->element_code,
                        'element_name' => $entry->element_name,
                        'processing_priority' => $entry->processing_priority,
                    ];
                }
            }
            $p['rows'][$i]->entries = $retEntries;
        }

        usort($elements, function($a, $b) {
            $c = $a->processing_priority <=> $b->processing_priority;
            if ($c === 0) $c = $a->element_code <=> $b->element_code;
            return $c;
        });
        $p['elements'] = $elements;
        return $p;
    }

    function getEmployee($employeeId, Carbon $effective, UserPrincipal $user, ?array $options = null): ?array {
        $processType = $options['process-type'] ?? PROCESS_TYPE_PAYROLL;

        $q = $this->repo->getEmployees($user->company_id, $effective);
        $employee = $q->where('_.id', $employeeId)->first();
        if ($employee == null) return null;

        $allElements = $this->elementSvc->listAll($effective, $user, $processType === PROCESS_TYPE_RETROPAY);
        $allEntries = $this->listAll($processType, [$employee->employee_id], $effective);
        $allLinks = $this->linkSvc->listAll($effective, $user);
        $allSalaryBasis = $this->salaryBasisSvc->listAll($user);

        $entries = $this->getEntries(
            $employee, $allElements, $allLinks, $allEntries[$employee->employee_id] ?? [], $allSalaryBasis
        );

        return [
            "employee" => $employee,
            "entries" => $entries,
        ];
    }

    function getOne($id, Carbon $effective, ?array $columns, UserPrincipal $user, ?array $options = null): ?object
    {
        $q = $this->repo->getOne($id, $user->company_id, $effective, $columns);
        $item = null;
        foreach ($q->cursor() as $d) {
            if ($item === null) {
                $item = (object) [
                    'entry_id' => $d->entry_id,
                    'employee_id' => $d->employee_id,
                    'element_id' => $d->element_id,
                    'effective_start' => $d->p_effective_start,
                    'effective_end' => $d->p_effective_end,
                    'values' => [],
                ];
            }
            unset($d->entry_id, $d->employee_id, $d->element_id, $d->p_effective_start, $d->p_effective_end);
            $item->values[] = $d;
        }
        if ($item == null) return null;

        $q = $this->repo->getEmployees($user->company_id, $effective);
        $employee = $q->where('_.id', $item->employee_id)->first();
        $element = $this->elementSvc->getOne($item->element_id, $effective, null, $user, $options);
        $allLinks = $this->linkSvc->listAll($effective, $user);
        $allSalaryBasis = $this->salaryBasisSvc->listAll($user);

        [$ret] = $this->getEntry($employee, $element, $allLinks, [$item->element_id => [$item]], $allSalaryBasis, true);
        return $ret[0];
    }

    function getOneValue($valueId, Carbon $effective, UserPrincipal $user): ?object
    {
        $d = $this->valueSvc->getOne($valueId, $effective, null, $user);
        if ($d === null) return null;
        $entry = $this->getOne($d->entry_id, $effective, null, $user);
        $values = $entry !== null ? $entry->values : [];
        return array_find($values, function($v) use ($d) { return $v->value_id === $d->value_id; });
    }

    public function listAll(string $processType, array $employeeIds, Carbon $effective): array
    {
        if (empty($employeeIds)) return [];
        $ret = [];
        $q = $this->repo->listAll($processType, $employeeIds, $effective);

        $seqNo = 1;
        foreach ($q->cursor() as $d) {
            $entryId = $d->entry_id;
            $employeeId = $d->employee_id;
            $elementId = $d->element_id;

            if (!array_key_exists($employeeId, $ret)) $ret[$employeeId] = [];
            if (!array_key_exists($elementId, $ret[$employeeId])) $ret[$employeeId][$elementId] = [];

            if (!array_key_exists($entryId, $ret[$employeeId][$elementId])) {
                $ret[$employeeId][$elementId][$entryId] = (object) [
                    'entry_id' => $entryId,
                    'element_id' => $elementId,
                    'effective_start' => $d->p_effective_start,
                    'effective_end' => $d->p_effective_end,
                    'element_seq_no' => $seqNo,
                    'values' => [],
                ];
                $seqNo++;
            }
            unset($d->entry_id, $d->employee_id, $d->element_id, $d->p_effective_start, $d->p_effective_end);
            $ret[$employeeId][$elementId][$entryId]->values[] = $d;
        }
        return $ret;
    }

    private function getEntries(object $employee, array $allElements, array $allLinks, array $allEntries, array $allSalaryBasis): array
    {
        $checkSalaryBasis = $employee->salary_basis_id !== null;
        $ret = [];
        foreach ($allElements as $element) {
            [$items, $fromSalaryBasis] = $this->getEntry($employee, $element, $allLinks, $allEntries, $allSalaryBasis, $checkSalaryBasis);
            if ($items != null) {
                array_push($ret, ...$items);
                if ($fromSalaryBasis) $checkSalaryBasis = false;
            }
        }
        return $ret;
    }

    private function getEntry(object $employee, object $element, array $allLinks, array $allEntries, array $allSalaryBasis,
                              bool $checkSalaryBasis): array
    {
        $hasEntry = false;
        $fromSalaryBasis = false;

        $items = [(object) [
            'element_id' => $element->element_id,
            'element_code' => $element->element_code,
            'element_name' => $element->element_name,
            'processing_priority' => $element->processing_priority,
            'entry_id' => null,
            'effective_start' => null,
            'effective_end' => null,
            'value_from' => 'pay-element',
            'values' => array_map(function($iv) {
                return (object) [
                    'input_value_id' => $iv->input_value_id,
                    'value_code' => $iv->value_code,
                    'value_name' => $iv->value_name,
                    'data_type' => $iv->data_type,
                    'value_id' => null,
                    'effective_start' => null,
                    'effective_end' => null,
                    'default_value' => $this->getValueByType($iv->data_type, $iv->default_value),
                    'link_value' => null,
                    'entry_value' => null,
                ];
            }, $element->values),
        ]];

        // From element link
        $fromLink = $this->getFromLink($items[0], $employee, $element, $allLinks);
        if ($fromLink != null) {
            $items = [$fromLink];
            $hasEntry = true;
        }

        // From element entry
        $fromEntry = $this->getFromEntry($items[0], $element, $allEntries);
        if ($fromEntry != null) {
            $items = $fromEntry;
            $hasEntry = true;
        }

        // From salary basis
        if ($checkSalaryBasis) {
            $fromSb = $this->getFromSalaryBasis($items, $employee, $element, $allSalaryBasis);
            if ($fromSb != null) {
                $items = $fromSb;
                $fromSalaryBasis = true;
                $hasEntry = true;
            }
        }
        if (!$hasEntry) return [null, false];
        return [$items, $fromSalaryBasis];
    }

    private function getFromLink(object $data, object $employee, object $element, array $allLinks): ?object
    {
        $link = $this->linkSvc->getFirstMatch($employee, $element, $allLinks);
        if ($link == null) return null;

        $ret = clone $data;
        $ret->value_from = 'pay-element-link';

        $ret->values = array_map(function($v) { return clone $v; }, $data->values);
        $this->getValues($element->values, $link->values, function($i, $iv, $item) use (&$ret) {
            if (!empty($item->link_value)) {
                $ret->values[$i]->link_value = $this->getValueByType($iv->data_type, $item->link_value);
            }
        });
        return $ret;
    }

    private function getFromEntry(object $data, object $element, array $allEntries): ?array
    {
        if (!isset($allEntries[$element->element_id])) return null;

        $ret = [];
        foreach ($allEntries[$element->element_id] as $d) {
            $entry = clone $data;
            $entry->values = array_map(function($v) { return clone $v; }, $data->values);

            $entry->entry_id = $d->entry_id;
            $entry->effective_start = $d->effective_start;
            $entry->effective_end = $d->effective_end;
            $entry->value_from = 'pay-per-entry';

            $this->getValues($element->values, $d->values, function($j, $iv, $item) use (&$entry) {
                if (!empty($item->entry_value)) {
                    $entry->values[$j]->value_id = $item->value_id;
                    $entry->values[$j]->effective_start = $item->effective_start;
                    $entry->values[$j]->effective_end = $item->effective_end;
                    $entry->values[$j]->entry_value = $this->getValueByType($iv->data_type, $item->entry_value);
                }
            });
            $ret[] = $entry;
        }
        return !empty($ret) ? $ret : null;
    }

    private function getFromSalaryBasis(array $data, object $employee, object $element, array $allSalaryBasis): ?array
    {
        if ($employee->salary_basis_id === null || empty($employee->basic_salary)) return null;
        $sb = array_find($allSalaryBasis, function($d) use ($employee) {
            return $d->salary_basis_id === $employee->salary_basis_id;
        });
        if ($sb === null || $sb->element_id !== $element->element_id) return null;

        $ret = [];
        foreach ($data as $item) {
            $entry = clone $item;
            $entry->values = array_map(function($v) { return clone $v; }, $item->values);

            foreach ($element->values as $i => $iv) {
                if ($sb->input_value_id === $iv->input_value_id) {
                    $entry->values[$i]->entry_value = +$employee->basic_salary;
                }
            }
            $ret[] = $entry;
        }
        return $ret;
    }

    private function getValues(array $elementValues, array $dataValues, \Closure $cb)
    {
        foreach ($elementValues as $i => $iv) {
            $value = array_find($dataValues, function($v) use ($iv) {
                return $v->input_value_id === $iv->input_value_id;
            });
            if ($value !== null) {
                $cb($i, $iv, $value);
            }
        }
    }

    private function getValueByType(string $type, ?string $value)
    {
        if (empty($value)) return null;
        if ($type === 'N') return +$value;
        return $value;
    }

    function insert(array $data, UserPrincipal $user): object
    {
        $values = $data['values'] ?? [];
        unset($data['values']);

        $ret = parent::insert($data, $user);

        foreach ($values as $result) {
            $result['entry_id'] = $ret->new_id;
            $this->valueSvc->insert($result, $user);
        }
        return $ret;
    }

    function insertFromRetro(object $item, Carbon $entryStart, Carbon $entryEnd, array $elements, UserPrincipal $user): ?int
    {
        return $this->repo->insertFromRetro($item, $entryStart, $entryEnd, $elements, $user);
    }

    function deleteFromRetro($employeeId, Carbon $periodStart, Carbon $periodEnd)
    {
        $this->repo->deleteFromRetro($employeeId, $periodStart, $periodEnd);
    }
}

