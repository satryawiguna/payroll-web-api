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

class ElementLinkValue extends AbstractModel
{
    protected $table = 'pay_element_link_value';
    protected $primaryKey = 'value_id';
    public $incrementing = false;

    protected $fillable = [
        'value_id', 'company_id', 'effective_first', 'effective_start', 'effective_end', 'effective_last',
        'link_id', 'input_value_id', 'link_value', 'description',
        'created_by', 'updated_by',
    ];

    public array $selectable = [
        'value_id', 'effective_start', 'effective_end',
        'input_value_id', 'iv.value_name as input_value_name', 'iv.default_value', 'link_value',
        'description',
    ];
}
