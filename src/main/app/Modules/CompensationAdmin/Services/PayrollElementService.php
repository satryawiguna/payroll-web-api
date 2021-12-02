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
use App\Modules\CompensationAdmin\Repositories\PayrollElementRepository;
use Carbon\Carbon;

class PayrollElementService extends AbstractTrackingService
{
    protected PayrollElementRepository $repo;
    protected PayrollInputValueService $inputValueSvc;

    function __construct(PayrollElementRepository $repo, PayrollInputValueService $inputValueSvc)
    {
        $this->repo = $repo;
        $this->inputValueSvc = $inputValueSvc;
    }

    function listAll(Carbon $effective, UserPrincipal $user, bool $onlyHasRetro = false): array
    {
        return $this->repo->listAll($user->company_id, $effective, $onlyHasRetro);
    }

    function listCbx(Carbon $effective, array $criteria, UserPrincipal $user, ?array $options): array
    {
        return $this->repo->listCbx($user->company_id, $effective, $criteria, $options);
    }

    function listForProcess(Carbon $effective, UserPrincipal $user): array
    {
        return $this->repo->listForProcess($user->company_id, $effective);
    }

    function getOne($id, Carbon $effective, ?array $columns, UserPrincipal $user, ?array $options = null): ?object
    {
        $item = parent::getOne($id, $effective, $columns, $user, $options);
        if ($item === null) return null;
        $item->values = $this->inputValueSvc->getPerElement($id, $effective, $user);
        return $item;
    }

    function insert(array $data, UserPrincipal $user): object
    {
        $values = $data['values'];
        unset($data['values']);

        $data['last_entry_type'] = 'S';
        $ret = parent::insert($data, $user);

        foreach ($values as $value) {
            $value['element_id'] = $ret->new_id;
            $this->inputValueSvc->insert($value, $user);
        }
        return $ret;
    }

}
