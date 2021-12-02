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
use App\Modules\CompensationAdmin\Repositories\PayrollBalanceRepository;

class PayrollBalanceService extends AbstractService
{
    protected PayrollBalanceRepository $repo;
    protected PayrollBalanceFeedService $feedSvc;

    function __construct(PayrollBalanceRepository $repo, PayrollBalanceFeedService $feedSvc)
    {
        $this->repo = $repo;
        $this->feedSvc = $feedSvc;
    }

    function getOne($id, ?array $columns, UserPrincipal $user, ?array $options = null): ?object
    {
        $q = $this->repo->getOne($id, $user->company_id, $columns, $options);
        $item = $q->first();
        if ($item === null) return null;
        $item->feeds = $this->feedSvc->getPerBalance($id, $options['effective'], $user);
        return $item;
    }

    function insert(array $data, UserPrincipal $user): object
    {
        $feeds = $data['feeds'];
        unset($data['feeds']);

        $ret = parent::insert($data, $user);

        foreach ($feeds as $feed) {
            $feed['balance_id'] = $ret->new_id;
            $this->feedSvc->insert($feed, $user);
        }
        return $ret;
    }

}
