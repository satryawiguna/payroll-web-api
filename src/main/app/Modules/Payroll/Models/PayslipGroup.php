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

class PayslipGroup extends AbstractModel
{
    protected $table = 'pay_payslip_group';
    protected $primaryKey = 'group_id';
    public $incrementing = false;

    protected $fillable = [
        'group_id', 'company_id', 'group_name', 'group_type', 'hide_when_empty', 'seq_no',
        'created_by', 'updated_by',
    ];

}
