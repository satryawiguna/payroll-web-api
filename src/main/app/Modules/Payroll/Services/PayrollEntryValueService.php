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

use App\Core\Services\AbstractTrackingService;
use App\Modules\Payroll\Repositories\PayrollEntryValueRepository;

class PayrollEntryValueService extends AbstractTrackingService
{
    protected PayrollEntryValueRepository $repo;

    function __construct(PayrollEntryValueRepository $repo)
    {
        $this->repo = $repo;
    }

}
