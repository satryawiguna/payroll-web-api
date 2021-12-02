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

class PayrollPerProcess extends AbstractModel
{
    protected $table = 'pay_per_process';
    protected $primaryKey = 'per_process_id';

    protected $fillable = [
        'per_process_id', 'company_id', 'process_id', 'employee_id', 'period_start', 'period_end',
        'process_status', 'is_validated', 'salary_basis_id', 'basic_salary',
        'created_by', 'updated_by',
    ];

}
