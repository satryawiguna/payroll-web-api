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

namespace App\Modules\WorkStructure\Services;

use App\Core\Auth\UserPrincipal;
use App\Core\Services\AbstractService;
use App\Modules\WorkStructure\Repositories\GradeRepository;

class GradeService extends AbstractService
{
    protected GradeRepository $repo;

    function __construct(GradeRepository $repo)
    {
        $this->repo = $repo;
    }

    function listCbx(UserPrincipal $user): array
    {
        $criteria = ['columns' => ['id as grade_id', 'name as grade_name']];
        $q = $this->repo->getAll($user->company_id, $criteria);
        return $q->get()->toArray();
    }

}
