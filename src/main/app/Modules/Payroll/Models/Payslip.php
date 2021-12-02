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

class Payslip extends AbstractModel
{
    protected $table = 'pay_payslip';
    protected $primaryKey = 'payslip_id';
    public $incrementing = false;

    protected $fillable = [
        'payslip_id', 'company_id', 'group_id', 'element_id', 'balance_id', 'hide_when_empty', 'label', 'seq_no',
        'created_by', 'updated_by',
    ];

}
