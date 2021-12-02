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

class PayrollInputValue extends AbstractModel
{
    protected $table = 'pay_input_value';
    protected $primaryKey = 'input_value_id';
    public $incrementing = false;

    protected $fillable = [
        'input_value_id', 'company_id', 'effective_first', 'effective_start', 'effective_end', 'effective_last',
        'element_id', 'value_code', 'value_name', 'data_type', 'default_value', 'description',
        'created_by', 'updated_by',
    ];

    public array $selectable = [
        'input_value_id', 'effective_start', 'effective_end', 'value_code', 'value_name', 'data_type', 'default_value',
        'description',
        'RAW: _.company_id is null as is_read_only',
    ];
}
