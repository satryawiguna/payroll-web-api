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

namespace App\Modules\Personal\Repositories;

use App\Core\Repositories\AbstractRepository;
use App\Modules\CompensationAdmin\Models\PayrollGroup;
use App\Modules\Personal\Models\Employee;
use App\Modules\Personal\Models\Termination;
use App\Modules\WorkStructure\Models\Department;
use App\Modules\WorkStructure\Models\DepartmentMutation;
use App\Modules\WorkStructure\Models\Grade;
use App\Modules\WorkStructure\Models\Location;
use App\Modules\WorkStructure\Models\Office;
use App\Modules\WorkStructure\Models\Position;
use App\Modules\WorkStructure\Models\PositionMutation;
use App\Modules\WorkStructure\Models\Project;
use App\Modules\WorkStructure\Models\ProjectMutation;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;

class EmployeeRepository extends AbstractRepository
{
    protected Employee $model;

    function __construct(Employee $model)
    {
        $this->model = $model;
    }

    public function inquiry($companyId, ?Carbon $effective, ?array $criteria, ?array $options = null): Builder
    {
        return $this
            ->query($companyId, $criteria, $options)->leftJoin(Termination::table('x'), 'x.employee_id', '_.id')

            ->leftJoin(SB_PREFIX.'genders as g', 'g.id', '_.gender_id')
            ->leftJoin(SB_PREFIX.'religions as r', 'r.id', '_.religion_id')
            ->leftJoin(SB_PREFIX.'marital_status as m', 'm.id', '_.marital_status_id')
            ->leftJoin(DepartmentMutation::table('dep_m'), function(JoinClause $join) use ($effective) {
                $join->on('dep_m.employee_id', '_.id');
                $join->whereRaw('? between dep_m.mutation_date and dep_m.mutation_date_end', date_to_str($effective));
            })
            ->leftJoin(Department::table('dep'), 'dep.id', 'dep_m.work_unit_id')
            ->leftJoin(ProjectMutation::table('prj_m'), function(JoinClause $join) use ($effective) {
                $join->on('prj_m.employee_id', '_.id');
                $join->whereRaw('? between prj_m.mutation_date and prj_m.mutation_date_end', date_to_str($effective));
            })
            ->leftJoin(Project::table('prj'), 'prj.id', 'prj_m.project_id')
            ->leftJoin(Office::table('ofc'), 'ofc.id', '_.office_id')
            ->leftJoin(Location::table('loc'), 'loc.id', '_.work_area_id')
            ->leftJoin(PositionMutation::table('pos_m'), function(JoinClause $join) use ($effective) {
                $join->on('pos_m.employee_id', '_.id');
                $join->whereRaw('? between pos_m.mutation_date and pos_m.mutation_date_end', date_to_str($effective));
            })
            ->leftJoin(Position::table('pos'), 'pos.id', 'pos_m.position_id')
            ->leftJoin(Grade::table('grd'), 'grd.id', 'pos_m.grade_id')

            ->leftJoin(PayrollGroup::table('pg'), function(JoinClause $join) use ($effective) {
                $join->on('pg.pay_group_id', '_.pay_group_id');
                $join->whereRaw('? between pg.effective_first and pg.effective_last', date_to_str($effective));
            });
    }

}
