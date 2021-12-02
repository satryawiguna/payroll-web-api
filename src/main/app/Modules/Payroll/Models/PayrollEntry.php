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

class PayrollEntry extends AbstractModel
{
    protected $table = 'pay_per_entry';
    protected $primaryKey = 'entry_id';
    public $incrementing = false;

    protected $fillable = [
        'entry_id', 'company_id', 'effective_first', 'effective_start', 'effective_end', 'effective_last',
        'employee_id', 'element_id', 'ref_retro_result_id',
        'created_by', 'updated_by',
    ];

}
