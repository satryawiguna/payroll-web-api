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

namespace App\Modules\Payroll\Models;

use App\Core\Models\AbstractModel;

class PayrollProcessLog extends AbstractModel
{
    protected $table = 'pay_process_log';
    protected $primaryKey = 'log_id';

    protected $fillable = [
        'log_id', 'company_id', 'process_id', 'per_process_id', 'element_id',
        'key', 'description', 'severity', 'exception_info',
        'created_by', 'updated_by',
    ];
}
