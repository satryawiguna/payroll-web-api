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

namespace App\Modules\CompensationAdmin\Models;

use App\Core\Models\AbstractModel;

class SalaryBasis extends AbstractModel
{
    protected $table = 'pay_salary_basis';
    protected $primaryKey = 'salary_basis_id';
    public $incrementing = false;

    protected $fillable = [
        'salary_basis_id', 'company_id', 'salary_basis_code', 'salary_basis_name', 'element_id', 'input_value_id',
        'description',
        'created_by', 'updated_by',
    ];

    public array $selectable = [
        'salary_basis_id', 'salary_basis_code', 'salary_basis_name',
        'element_id', 'el.element_name', 'input_value_id', 'iv.value_name as input_value_name',
        'description',
        'RAW: _.company_id is null as is_read_only',
    ];

    public array $searchable = ['salary_basis_code', 'salary_basis_name'];

    public array $sortable = ['salary_basis_code'];
}
