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

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string[] $selectable
 * @property string[] $searchable
 * @property string[] $sortable
 */
abstract class AbstractModel extends Model
{
    static function table(?string $alias = null): string
    {
        $table = (new static)->getTable();
        return ($alias !== null) ? $table.' as '.$alias : $table;
    }
}
