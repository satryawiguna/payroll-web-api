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

class PayrollBalanceFeed extends AbstractModel
{
    protected $table = 'pay_balance_feed';
    protected $primaryKey = 'feed_id';
    public $incrementing = false;

    protected $fillable = [
        'feed_id', 'company_id', 'effective_first', 'effective_start', 'effective_end', 'effective_last',
        'balance_id', 'classification_id', 'element_id', 'add_subtract',
        'created_by', 'updated_by',
    ];

    public array $selectable = [
        'feed_id', 'effective_start', 'effective_end',
        'classification_id', 'c.classification_name', 'element_id', 'el.element_name', 'add_subtract',
        'RAW: _.company_id is null as is_read_only',
    ];
}
