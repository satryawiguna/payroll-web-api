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

use App\Core\Repositories\AbstractRepository;
use App\Modules\Payroll\Models\PayrollProcessResultValue;

class PayrollProcessResultValueRepository extends AbstractRepository
{
    protected PayrollProcessResultValue $model;

    function __construct(PayrollProcessResultValue $model)
    {
        $this->model = $model;
    }
}
