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

class ElementClassification extends AbstractModel
{
    protected $table = 'pay_element_classification';
    protected $primaryKey = 'classification_id';
    public $incrementing = false;

    protected $fillable = [
        'classification_id', 'company_id', 'classification_name', 'default_priority', 'description',
        'created_by', 'updated_by',
    ];

    public array $selectable = [
        'classification_id', 'classification_name', 'default_priority', 'description',
        'RAW: _.company_id is null as is_read_only',
    ];

    public array $searchable = ['classification_name'];

    public array $sortable = ['default_priority'];
}
