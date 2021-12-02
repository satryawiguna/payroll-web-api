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
use App\Modules\CompensationAdmin\Models\ElementLinkValue;
use App\Modules\CompensationAdmin\Models\PayrollElement;
use App\Modules\CompensationAdmin\Models\PayrollInputValue;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class PayrollInputValueRepository extends AbstractTrackingRepository
{
    protected PayrollInputValue $model;

    function __construct(PayrollInputValue $model)
    {
        $this->model = $model;
    }

    function getPerElement($elementId, $companyId, Carbon $effective, ?array $options): array
    {
        return $this
            ->inquiry($companyId, $effective, ['sort' => 'value_code'], $options)
            ->where('element_id', $elementId)
            ->get()->toArray();
    }

    function allInputValues($companyId, Carbon $effective, bool $hasRetroOnly = false, ?array $columns = null): array
    {
        $q = $this->query($companyId, [
            'columns' => $columns ?? [
                'input_value_id', 'element_id', 'value_code', 'value_name', 'default_value', 'data_type'
            ],
            'sorts' => ['element_id', 'value_code']])
            ->whereRaw('? between _.effective_start and _.effective_end', date_to_str($effective));

        if ($hasRetroOnly) {
            $q->join(PayrollElement::table('el'), function(JoinClause $join) use ($effective) {
                $join->on('el.element_id', '_.element_id');
                $join->whereRaw('? between el.effective_start and el.effective_end', date_to_str($effective));
            });
            $q->whereNotNull('el.retro_element_id');
        }

        $ret = [];
        foreach ($q->cursor() as $item) {
            $elementId = $item->element_id;
            unset($item->element_id);
            if (!array_key_exists($elementId, $ret)) $ret[$elementId] = [];
            $ret[$elementId][] = $item;
        }
        return $ret;
    }

    function deleteById($id): int
    {
        $this->deleteLinkValues($id);
        return $this->delete($this->getPrimaryKey().' = ?', $id);
    }

    function deleteLinkValues($inputValueId): int {
        return DB::table(ElementLinkValue::table())->where('input_value_id', $inputValueId)->delete();
    }

}
