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

class PayrollFormula extends AbstractModel
{
    protected $table = 'pay_formula';
    protected $primaryKey = 'formula_id';
    public $incrementing = false;

    protected $fillable = [
        'formula_id', 'company_id', 'effective_first', 'effective_start', 'effective_end', 'effective_last',
        'formula_name', 'element_id', 'formula_type', 'formula_def', 'description',
        'created_by', 'updated_by',
    ];

    public array $selectable = [
        'formula_id', 'effective_start', 'effective_end', 'formula_name', 'element_id', 'el.element_name', 'formula_type',
        "RAW: ".
        "case when formula_type = 'FX' then (".
            "select group_concat(r.formula_expr separator ', ') ".
            'from pay_formula_result r '.
            'where r.formula_id = _.formula_id and _.effective_start between r.effective_start and r.effective_end'.
        ') else _.formula_def end as formula_def',
        'RAW: ('.
            "select group_concat(distinct el.element_name order by el.processing_priority separator ', ') ".
            'from pay_formula_result r '.
            '     join pay_element el on el.element_id = r.element_id and _.effective_start between el.effective_start and el.effective_end '.
            'where r.formula_id = _.formula_id and _.effective_start between r.effective_start and r.effective_end '.
        ') as result_elements',
        'description',
        'RAW: _.company_id is null as is_read_only',
    ];

    public array $searchable = ['formula_name'];

    public array $sortable = ['formula_name'];
}
