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

use App\Core\Repositories\AbstractRepository;
use App\Modules\CompensationAdmin\Models\PayrollBalance;

class PayrollBalanceRepository extends AbstractRepository
{
    protected PayrollBalance $model;

    function __construct(PayrollBalance $model)
    {
        $this->model = $model;
    }
}
