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
use App\Modules\CompensationAdmin\Repositories\ElementClassificationRepository;

class ElementClassificationService extends AbstractService
{
    protected ElementClassificationRepository $repo;

    function __construct(ElementClassificationRepository $repo)
    {
        $this->repo = $repo;
    }

    function listCbx(UserPrincipal $user): array
    {
        $criteria = ['columns' => ['classification_id', 'classification_name', 'default_priority']];
        $q = $this->repo->getAll($user->company_id, $criteria);
        return $q->get()->toArray();
    }

}
