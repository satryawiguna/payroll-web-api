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

namespace App\Modules\WorkStructure\Repositories;

use App\Core\Repositories\AbstractRepository;
use App\Modules\WorkStructure\Models\Office;

class OfficeRepository extends AbstractRepository
{
    protected Office $model;

    function __construct(Office $model)
    {
        $this->model = $model;
    }
}
