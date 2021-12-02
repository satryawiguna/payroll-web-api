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

class PayrollBalance extends AbstractModel
{
    protected $table = 'pay_balance';
    protected $primaryKey = 'balance_id';
    public $incrementing = false;

    protected $fillable = [
        'balance_id', 'company_id', 'balance_name', 'balance_feed_type', 'description',
        'created_by', 'updated_by',
    ];

    public array $selectable = [
        'balance_id', 'balance_name', 'balance_feed_type', 'description',
        'RAW: _.company_id is null as is_read_only'
    ];

    public array $searchable = ['balance_name'];

    public array $sortable = ['balance_name'];

}
