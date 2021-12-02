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
use App\Modules\CompensationAdmin\Repositories\PayrollBalanceFeedRepository;
use Carbon\Carbon;

class PayrollBalanceFeedService extends AbstractTrackingService
{
    protected PayrollBalanceFeedRepository $repo;

    function __construct(PayrollBalanceFeedRepository $repo)
    {
        $this->repo = $repo;
    }

    function getPerBalance($balanceId, Carbon $effective, UserPrincipal $user): array
    {
        return $this->repo->getPerBalance($balanceId, $user->company_id, $effective);
    }

}
