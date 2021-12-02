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
use App\Modules\WorkStructure\Models\Location;

class LocationRepository extends AbstractRepository
{
    protected Location $model;

    function __construct(Location $model)
    {
        $this->model = $model;
    }
}
