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
use App\Modules\WorkStructure\Models\Project;

class ProjectRepository extends AbstractRepository
{
    protected Project $model;

    function __construct(Project $model)
    {
        $this->model = $model;
    }
}

