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

namespace App\Modules\CompensationAdmin\Services;

use App\Core\Auth\UserPrincipal;
use App\Core\Services\AbstractService;
use App\Modules\CompensationAdmin\Repositories\SalaryBasisRepository;

class SalaryBasisService extends AbstractService
{
    protected SalaryBasisRepository $repo;

    function __construct(SalaryBasisRepository $repo)
    {
        $this->repo = $repo;
    }
    function getPage(?array $criteria, UserPrincipal $user, ?array $options = null): array
    {
        $q = $this->repo->getAll($user->company_id, $options['effective'], $criteria, $options);
        return $this->paginate($q, $criteria['per_page']);
    }

    function listAll(UserPrincipal $user): array
    {
        return $this->repo->listAll($user->company_id);
    }

    function getOne($id, ?array $columns, UserPrincipal $user, ?array $options = null): ?object
    {
        $q = $this->repo->getOne($id, $options['effective'], $columns, $options);
        return $q->first();
    }

}
