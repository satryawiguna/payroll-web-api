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
use App\Modules\CompensationAdmin\Models\ElementClassification;
use App\Modules\CompensationAdmin\Models\PayrollElement;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;

class PayrollElementRepository extends AbstractTrackingRepository
{
    protected PayrollElement $model;
    protected PayrollFormulaRepository $formulaRepo;
    protected PayrollFormulaResultRepository $formulaResultRepo;
    protected PayrollInputValueRepository $inputValueRepo;

    function __construct(PayrollElement $model, PayrollFormulaRepository $fnRepo, PayrollFormulaResultRepository $fnResultRepo,
                         PayrollInputValueRepository $inputValueRepo)
    {
        $this->model = $model;
        $this->formulaRepo = $fnRepo;
        $this->formulaResultRepo = $fnResultRepo;
        $this->inputValueRepo = $inputValueRepo;
    }

    protected function inquiry($companyId, ?Carbon $effective, ?array $criteria, ?array $options = null): Builder
    {
        return $this
            ->query($companyId, $effective, $criteria, $options)
            ->join(ElementClassification::table('c'), 'c.classification_id', '_.classification_id')
            ->leftJoin(PayrollElement::table('re'), function(JoinClause $join) use ($effective) {
                $join->on('re.element_id', '_.retro_element_id');
                $join->whereRaw('? between re.effective_first and re.effective_last', date_to_str($effective));
            });
    }

    function listCbx($companyId, Carbon $effective, array $criteria, ?array $options = null): array
    {
        $ret = [];

        $includeValues = $options['include-values'] ?? false;
        $inputValues = $includeValues
            ? $this->inputValueRepo->allInputValues($companyId, $effective, false,
                ['input_value_id', 'element_id', 'value_code', 'value_name', 'data_type', 'default_value'])
            : [];

        $criteria['columns'] = ['element_id', 'element_code', 'element_name', 'c.classification_name'];
        $q = $this
            ->query($companyId, $effective, $criteria, $options)
            ->join(ElementClassification::table('c'), 'c.classification_id', '_.classification_id');

        foreach ($q->cursor() as $item) {
            if ($includeValues) $item->values = $inputValues[$item->element_id] ?? [];
            $ret[] = $item;
        }
        return $ret;
    }

    function listAll($companyId, Carbon $effective, bool $onlyHasRetro): array
    {
        $ret = [];
        $inputValues = $this->inputValueRepo->allInputValues($companyId, $effective, $onlyHasRetro);

        $q = $this->query($companyId, [
            'columns' => ['element_id', 'element_code', 'element_name', 'processing_priority', 'retro_element_id'],
            'sorts' => ['processing_priority']])
            ->whereRaw('? between _.effective_start and _.effective_end', date_to_str($effective));

        if ($onlyHasRetro) {
            $q->whereNotNull('_.retro_element_id');
        }

        foreach ($q->cursor() as $item) {
            $item->values = $inputValues[$item->element_id] ?? [];
            $ret[] = $item;
        }
        return $ret;
    }

    function listForProcess($companyId, Carbon $effective): array
    {
        $ret = [];
        $allFormula = $this->formulaRepo->allFormulasPerElement($companyId, $effective);
        $allInputValues = $this->inputValueRepo->allInputValues($companyId, $effective);

        $q = $this->query($companyId, $effective, [
            'columns' => [
                'element_id', 'element_code', 'last_entry_type', 'is_recurring', 'is_once_per_period',
                'formula_id', 'f.formula_type', 'f.formula_def'
            ],
            'sorts' => ['processing_priority']]);

        foreach ($q->cursor() as $item) {
            $formulas = $allFormula[$item->element_id] ?? [];
            $element = (object) [
                'element_id' => $item->element_id,
                'element_code' => $item->element_code,
                'last_entry_type' => $item->last_entry_type,
                'formulas' => $formulas,
                'triggers' => [],
                'is_recurring' => $item->is_recurring === 1,
                'is_once_per_period' => $item->is_once_per_period === 1,
                'values' => $allInputValues[$item->element_id] ?? [],
            ];
            foreach ($formulas as $formula) {
                foreach ($formula->results as $result) {
                    $resElementId = $result->element_id;
                    if ($element->element_id !== $resElementId && !in_array($resElementId, $element->triggers)) {
                        $element->triggers[] = $resElementId;
                    }
                }
            }
            $ret[$item->element_id] = $element;
        }
        return $ret;
    }

}
