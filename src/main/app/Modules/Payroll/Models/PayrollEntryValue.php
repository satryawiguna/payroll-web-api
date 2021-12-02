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

class PayrollEntryValue extends AbstractModel
{
    protected $table = 'pay_per_entry_value';
    protected $primaryKey = 'value_id';
    public $incrementing = false;

    protected $fillable = [
        'value_id', 'company_id', 'effective_first', 'effective_start', 'effective_end', 'effective_last',
        'entry_id', 'input_value_id', 'entry_value',
        'created_by', 'updated_by',
    ];
}
