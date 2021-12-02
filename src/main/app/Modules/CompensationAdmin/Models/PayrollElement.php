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

class PayrollElement extends AbstractModel
{
    protected $table = 'pay_element';
    protected $primaryKey = 'element_id';
    public $incrementing = false;

    protected $fillable = [
        'element_id', 'company_id', 'effective_first', 'effective_start', 'effective_end', 'effective_last',
        'element_code', 'element_name',
        'classification_id', 'last_entry_type', 'processing_priority', 'retro_element_id',
        'is_recurring', 'is_once_per_period', 'description',
        'created_by', 'updated_by',
    ];

    public array $selectable = [
        'element_id', 'effective_start', 'effective_end', 'element_code', 'element_name',
        'RAW: ('.
            "select group_concat(iv.value_name order by iv.value_code separator ', ') ".
            'from pay_input_value iv '.
            'where iv.element_id = _.element_id and _.effective_start between iv.effective_first and iv.effective_last '.
        ') as input_value_names',
        'classification_id', 'c.classification_name', 'last_entry_type', 'processing_priority',
        'retro_element_id', 're.element_name as retro_element_name',
        'is_recurring', 'is_once_per_period', 'description',
        'RAW: _.company_id is null as is_read_only',
    ];

    public array $searchable = ['element_code', 'element_name', 'c.classification_name'];

    public array $sortable = ['processing_priority', 'element_code'];
}
