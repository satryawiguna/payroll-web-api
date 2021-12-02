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
use App\Modules\CompensationAdmin\Repositories\PayrollFormulaRepository;
use Carbon\Carbon;

class PayrollFormulaService extends AbstractTrackingService
{
    protected PayrollFormulaRepository $repo;
    protected PayrollFormulaResultService $resultSvc;

    function __construct(PayrollFormulaRepository $repo, PayrollFormulaResultService $resultSvc)
    {
        $this->repo = $repo;
        $this->resultSvc = $resultSvc;
    }

    function listCbx(Carbon $effective, UserPrincipal $user): array
    {
        $criteria = ['columns' => ['formula_id', 'formula_name']];
        $q = $this->repo->getAll($user->company_id, $effective, $criteria);
        return $q->get()->toArray();
    }

    function getOne($id, Carbon $effective, ?array $columns, UserPrincipal $user, ?array $options = null): ?object
    {
        $item = parent::getOne($id, $effective, $columns, $user, $options);
        if ($item === null) return null;
        $item->results = $this->resultSvc->getPerFormula($id, $effective, $user);
        return $item;
    }

    function insert(array $data, UserPrincipal $user): object
    {
        $results = $data['results'];
        unset($data['results']);

        $ret = parent::insert($data, $user);

        foreach ($results as $result) {
            $result['formula_id'] = $ret->new_id;
            $this->resultSvc->insert($result, $user);
        }
        return $ret;
    }

}
