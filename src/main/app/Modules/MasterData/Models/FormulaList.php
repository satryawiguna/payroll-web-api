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

namespace App\Modules\MasterData\Models;

use App\Core\Models\AbstractModel;

class FormulaList extends AbstractModel
{
    protected $table = 'db_formula_list';
    protected $primaryKey = 'formula_name';

    protected $fillable = [
        'formula_name', 'formula_category', 'parameters', 'description',
        'created_by', 'updated_by',
    ];

    public array $selectable = [
        'formula_name', 'formula_category', 'description',
    ];
}
