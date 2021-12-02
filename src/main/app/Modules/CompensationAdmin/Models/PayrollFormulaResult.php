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

class PayrollFormulaResult extends AbstractModel
{
    protected $table = 'pay_formula_result';
    protected $primaryKey = 'result_id';
    public $incrementing = false;

    protected $fillable = [
        'result_id', 'company_id', 'effective_first', 'effective_start', 'effective_end', 'effective_last',
        'formula_id', 'result_code', 'element_id', 'input_value_id', 'formula_expr',
        'created_by', 'updated_by',
    ];

    public array $selectable = [
        'result_id', 'effective_start', 'effective_end', 'result_code',
        'element_id', 'el.element_name', 'input_value_id', 'iv.value_name as input_value_name',
        'formula_expr',
        'RAW: _.company_id is null as is_read_only',
    ];
}
