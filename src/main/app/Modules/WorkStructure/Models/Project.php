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

namespace App\Modules\WorkStructure\Models;

use App\Core\Models\AbstractModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends AbstractModel
{
    use SoftDeletes;

    protected $table = SB_PREFIX.'projects';

    public array $sortable = ['name'];
}
