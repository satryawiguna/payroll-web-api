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

use App\Core\Repositories\AbstractTrackingRepository;
use App\Modules\Payroll\Models\PayrollEntryValue;

class PayrollEntryValueRepository extends AbstractTrackingRepository
{
    protected PayrollEntryValue $model;

    function __construct(PayrollEntryValue $model)
    {
        $this->model = $model;
    }
}
