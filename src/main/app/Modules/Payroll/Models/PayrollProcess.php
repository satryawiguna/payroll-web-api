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

class PayrollProcess extends AbstractModel
{
    protected $table = 'pay_process';
    protected $primaryKey = 'process_id';

    protected $fillable = [
        'process_id', 'company_id', 'process_type',
        'batch_name', 'process_date', 'period_start', 'period_end', 'ret_entry_period_start', 'ret_entry_period_end',
        'description', 'filter_info', 'is_validated',
        'created_by', 'updated_by',
    ];
}
