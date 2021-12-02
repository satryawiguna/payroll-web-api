<?php

namespace App\Modules\CompensationAdmin\Services;

use App\Core\Auth\UserPrincipal;
use App\Core\Services\AbstractTrackingService;
use App\Modules\CompensationAdmin\Repositories\PayrollInputValueRepository;
use Carbon\Carbon;

class PayrollInputValueService extends AbstractTrackingService
{
    protected PayrollInputValueRepository $repo;

    function __construct(PayrollInputValueRepository $repo)
    {
        $this->repo = $repo;
    }

    function getPerElement($elementId, Carbon $effective, UserPrincipal $user, ?array $options = null): array
    {
        return $this->repo->getPerElement($elementId, $user->company_id, $effective, $options);
    }

}
