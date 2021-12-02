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

namespace App\Modules\CompensationAdmin\Repositories;

use App\Core\Repositories\AbstractTrackingRepository;
use App\Modules\CompensationAdmin\Models\ElementClassification;
use App\Modules\CompensationAdmin\Models\ElementLink;
use App\Modules\CompensationAdmin\Models\PayrollElement;
use App\Modules\CompensationAdmin\Models\PayrollGroup;
use App\Modules\Personal\Models\Employee;
use App\Modules\WorkStructure\Models\Department;
use App\Modules\WorkStructure\Models\Grade;
use App\Modules\WorkStructure\Models\Location;
use App\Modules\WorkStructure\Models\Office;
use App\Modules\WorkStructure\Models\Position;
use App\Modules\WorkStructure\Models\Project;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;

class ElementLinkRepository extends AbstractTrackingRepository
{
    protected ElementLink $model;
    private ElementLinkValueRepository $valueRepo;

    function __construct(ElementLink $model, ElementLinkValueRepository $valueRepo)
    {
        $this->model = $model;
        $this->valueRepo = $valueRepo;
    }

    protected function inquiry($companyId, ?Carbon $effective, ?array $criteria, ?array $options = null): Builder
    {
        $filterFields = [
            'office_id', 'location_id', 'department_id', 'project_id', 'position_id', 'grade_id', 'pay_group_id',
            'people_group', 'employee_category'
        ];
        [$criteria, $filters] = $this->extractCriteria($filterFields, $criteria);

        $q = $this
            ->query($companyId, $effective, $criteria, $options)
            ->join(PayrollElement::table('el'), function(JoinClause $join) use ($effective) {
                $join->on('el.element_id', '_.element_id');
                $join->whereRaw('? between el.effective_first and el.effective_last', date_to_str($effective));
            })
            ->join(ElementClassification::table('cl'), 'cl.classification_id', 'el.classification_id')
            ->leftJoin(Office::table('of'), 'of.id', '_.office_id')
            ->leftJoin(Location::table('lc'), 'lc.id', '_.location_id')
            ->leftJoin(Department::table('dp'), 'dp.id', '_.department_id')
            ->leftJoin(Project::table('pj'), 'pj.id', '_.project_id')
            ->leftJoin(Position::table('ps'), 'ps.id', '_.position_id')
            ->leftJoin(Grade::table('gd'), 'gd.id', '_.grade_id')
            ->leftJoin(PayrollGroup::table('pg'), function(JoinClause $join) use ($effective) {
                $join->on('pg.pay_group_id', '_.pay_group_id');
                $join->whereRaw('? between pg.effective_first and pg.effective_last', date_to_str($effective));
            })
            ->leftJoin(Employee::table('em'), 'em.id', '_.employee_id');

        foreach ($filterFields as $field) {
            $this->addFilter($q, $field, $filters);
        }
        return $q;
    }

    private function extractCriteria(array $fields, ?array $criteria): array {
        if (!isset($criteria['filters'])) return [null, []];

        $filters = [];
        foreach ($criteria['filters'] as $i => $filter) {
            foreach ($fields as $j => $field) {
                if ($filter['field'] === $field) {
                    $filters[$field] = $filter['value'];
                    unset($criteria['filters'][$i], $fields[$j]);
                }
            }
        }
        return [$criteria, $filters];
    }

    function addFilter(Builder $q, string $field, array $filters) {
        $value = $filters[$field] ?? null;
        if ($value === null) return;
        $q->where(function(Builder $q) use ($field, $value) {
            $q->orWhereNull('_.'.$field);
            $q->orWhere('_.'.$field, $value);
        });
    }

    function listAll($companyId, Carbon $effective): array
    {
        $ret = [];
        $values = $this->valueRepo->allValues($companyId, $effective);

        $q = $this->query($companyId, [
            'columns' => [
                'link_id', 'element_id', 'office_id', 'location_id', 'department_id', 'project_id', 'position_id',
                'grade_id', 'pay_group_id', 'people_group', 'employee_category', 'employee_id'
            ],
            'sorts' => [
                'element_id', 'office_id', 'location_id', 'department_id', 'project_id', 'position_id', 'grade_id',
                'pay_group_id', 'people_group', 'employee_category', 'employee_id'
            ]])
            ->whereRaw('? between _.effective_start and _.effective_end', date_to_str($effective));

        foreach ($q->cursor() as $item) {
            $item->values = $values[$item->link_id] ?? [];
            $ret[] = $item;
        }
        return $ret;
    }

}
