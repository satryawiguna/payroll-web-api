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

class PayrollProcessResult extends AbstractModel
{
    protected $table = 'pay_per_process_result';
    protected $primaryKey = 'result_id';

    protected $fillable = [
        'result_id', 'company_id', 'per_process_id', 'element_id', 'element_seq_no', 'parent_element_id', 'element_code',
        'period_start', 'period_end', 'ref_entry_id',
        'pay_value', 'retro_value', 'division', 'division_type', 'description', 'retro_has_processed',
        'created_by', 'updated_by',
    ];

}
