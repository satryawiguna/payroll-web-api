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
use App\Core\Services\AbstractTrackingService;
use App\Modules\CompensationAdmin\Repositories\ElementLinkValueRepository;
use Carbon\Carbon;

class ElementLinkValueService extends AbstractTrackingService
{
    protected ElementLinkValueRepository $repo;

    function __construct(ElementLinkValueRepository $repo)
    {
        $this->repo = $repo;
    }

    function getPerLink($linkId, $elementId, Carbon $effective, UserPrincipal $user, ?array $options = null): array
    {
        return $this->repo->getPerLink($linkId, $elementId, $user->company_id, $effective, $options);
    }

}
