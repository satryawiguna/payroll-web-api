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

namespace App\Modules\CompensationAdmin\Services;

use App\Core\Auth\UserPrincipal;
use App\Core\Services\AbstractTrackingService;
use App\Modules\CompensationAdmin\Repositories\PayrollGroupRepository;
use Carbon\Carbon;

class PayrollGroupService extends AbstractTrackingService
{
    protected PayrollGroupRepository $repo;

    function __construct(PayrollGroupRepository $repo)
    {
        $this->repo = $repo;
    }

    function listCbx(Carbon $effective, UserPrincipal $user): array
    {
        $criteria = ['columns' => ['pay_group_id', 'pay_group_name']];
        $q = $this->repo->getAll($user->company_id, $effective, $criteria);
        return $q->get()->toArray();
    }

}
