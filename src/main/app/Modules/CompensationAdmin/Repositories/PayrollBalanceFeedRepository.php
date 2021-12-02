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
use App\Modules\CompensationAdmin\Models\PayrollBalanceFeed;
use App\Modules\CompensationAdmin\Models\PayrollElement;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;

class PayrollBalanceFeedRepository extends AbstractTrackingRepository
{
    protected PayrollBalanceFeed $model;

    function __construct(PayrollBalanceFeed $model)
    {
        $this->model = $model;
    }

    function getPerBalance($balanceId, $companyId, Carbon $effective, ?array $options = null): array
    {
        return $this
            ->inquiry($companyId, $effective, $options)
            ->where('balance_id', $balanceId)
            ->get()->toArray();
    }

    protected function inquiry($companyId, ?Carbon $effective, ?array $criteria, ?array $options = null): Builder
    {
        return $this
            ->query($companyId, $effective, ['sort' => ['c.default_priority', 'el.processing_priority']], $options)
            ->leftJoin(ElementClassification::table('c'), 'c.classification_id', '_.classification_id')
            ->leftJoin(PayrollElement::table('el'), function(JoinClause $join) use ($effective) {
                $join->on('el.element_id', '_.element_id');
                $join->whereRaw('? between el.effective_first and el.effective_last', date_to_str($effective));
            });
    }
}
