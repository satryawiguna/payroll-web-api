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
use App\Modules\CompensationAdmin\Repositories\ElementLinkRepository;
use Carbon\Carbon;

class ElementLinkService extends AbstractTrackingService
{
    protected ElementLinkRepository $repo;
    protected ElementLinkValueService $valueSvc;

    function __construct(ElementLinkRepository $repo, ElementLinkValueService $valueSvc)
    {
        $this->repo = $repo;
        $this->valueSvc = $valueSvc;
    }

    function listAll(Carbon $effective, UserPrincipal $user): array
    {
        return $this->repo->listAll($user->company_id, $effective);
    }

    function getOne($id, Carbon $effective, ?array $columns, UserPrincipal $user, ?array $options = null): ?object
    {
        $item = parent::getOne($id, $effective, $columns, $user, $options);
        if ($item === null) return null;
        $item->values = $this->valueSvc->getPerLink($id, $item->element_id, $effective, $user);
        return $item;
    }

    function insert(array $data, UserPrincipal $user): object
    {
        $values = $data['values'];
        unset($data['values']);

        $ret = parent::insert($data, $user);

        foreach ($values as $value) {
            $value['element_id'] = $ret->new_id;
            $this->valueSvc->insert($value, $user);
        }
        return $ret;
    }

    function getFirstMatch(object $employee, object $element, array $links): ?object
    {
        foreach ($links as $link) {
            if (($link->element_id === $element->element_id)
                && ($link->office_id === null || $link->office_id === $employee->office_id)
                && ($link->location_id === null || $link->location_id == $employee->location_id)
                && ($link->department_id === null || $link->department_id == $employee->department_id)
                && ($link->project_id === null || $link->project_id == $employee->project_id)
                && ($link->position_id === null || $link->position_id == $employee->position_id)
                && ($link->grade_id === null || $link->grade_id == $employee->grade_id)
                && ($link->pay_group_id === null || $link->pay_group_id == $employee->pay_group_id)
                && ($link->people_group === null || $link->people_group == $employee->people_group)
                && ($link->employee_category === null || $link->employee_category == $employee->employee_category)
                && ($link->employee_id === null || $link->employee_id == $employee->employee_id)) {

                return $link;
            }
        }
        return null;
    }

}
