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

class PayrollGroup extends AbstractModel
{
    protected $table = 'pay_group';
    protected $primaryKey = 'pay_group_id';
    public $incrementing = false;

    protected $fillable = [
        'pay_group_id', 'company_id', 'effective_first', 'effective_start', 'effective_end', 'effective_last',
        'pay_group_name', 'description', 'created_by', 'updated_by',
    ];

    public array $selectable = [
        'pay_group_id', 'effective_start', 'effective_end', 'pay_group_name', 'description',
        'RAW: _.company_id is null as is_read_only',
    ];

    public array $searchable = ['pay_group_name'];

    public array $sortable = ['pay_group_name'];
}
