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

namespace App\Modules\Personal\Models;

use App\Core\Models\AbstractModel;

class EmployeeSalary extends AbstractModel
{
    protected $table = 'per_basic_salary';
    protected $primaryKey = 'salary_id';
    public $incrementing = false;

    protected $fillable = [
        'salary_id', 'company_id', 'employee_id', 'change_date', 'date_to', 'basic_salary', 'change_reason', 'description',
        'created_by', 'updated_by',
    ];

}
