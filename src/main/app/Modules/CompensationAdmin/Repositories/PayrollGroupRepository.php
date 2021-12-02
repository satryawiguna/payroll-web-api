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
use App\Modules\CompensationAdmin\Models\PayrollGroup;

class PayrollGroupRepository extends AbstractTrackingRepository
{
    protected PayrollGroup $model;

    function __construct(PayrollGroup $model)
    {
        $this->model = $model;
    }

}
