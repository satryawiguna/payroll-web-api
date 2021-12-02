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
use App\Modules\Payroll\Models\PayrollProcessLog;

class PayrollProcessLogRepository extends AbstractRepository
{
    protected PayrollProcessLog $model;

    function __construct(PayrollProcessLog $model)
    {
        $this->model = $model;
    }

    function clearLog($processId, $perProcessId)
    {
        $this->delete('process_id = ? and per_process_id = ?', $processId, $perProcessId);
    }
}
