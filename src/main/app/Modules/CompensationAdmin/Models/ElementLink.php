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

class ElementLink extends AbstractModel
{
    protected $table = 'pay_element_link';
    protected $primaryKey = 'link_id';
    public $incrementing = false;

    protected $fillable = [
        'link_id', 'company_id', 'effective_first', 'effective_start', 'effective_end', 'effective_last',
        'element_id',
        'office_id', 'location_id', 'department_id', 'project_id', 'position_id', 'grade_id', 'pay_group_id',
        'people_group', 'employee_category', 'employee_id',
        'description',
        'created_by', 'updated_by',
    ];

    public array $selectable = [
        'link_id', 'effective_start', 'effective_end',
        'element_id', 'el.element_name', 'cl.classification_name',
        'office_id', 'of.name as office_name','location_id', 'lc.title as location_name',
        'department_id', 'dp.title as department_name', 'project_id', 'pj.name as project_name',
        'position_id', 'ps.name as position_name', 'grade_id', 'gd.name as grade_name',
        'pay_group_id', 'pg.pay_group_name', 'people_group', 'employee_category',
        'employee_id', 'em.full_name as employee_name',
        'description',
        'RAW: _.company_id is null as is_read_only',
    ];

    public array $searchable = ['el.element_name', 'cl.classification_name'];

    public array $sortable = ['el.processing_priority', 'el.element_code'];
}
