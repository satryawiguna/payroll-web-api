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
use App\Modules\CompensationAdmin\Repositories\PayrollFormulaResultRepository;
use Carbon\Carbon;

class PayrollFormulaResultService extends AbstractTrackingService
{
    protected PayrollFormulaResultRepository $repo;

    function __construct(PayrollFormulaResultRepository $repo)
    {
        $this->repo = $repo;
    }

    function getPerFormula($formulaId, Carbon $effective, UserPrincipal $user): array
    {
        return $this->repo->getPerFormula($formulaId, $user->company_id, $effective);
    }

}
