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

class PayrollProcessResultValue extends AbstractModel
{
    protected $table = 'pay_per_process_result_value';
    protected $primaryKey = 'value_id';

    protected $fillable = [
        'value_id', 'company_id', 'result_id', 'input_value_id', 'value_code', 'value',
        'created_by', 'updated_by',
    ];

}
