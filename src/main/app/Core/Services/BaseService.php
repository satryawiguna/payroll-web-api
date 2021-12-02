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

namespace App\Core\Services;

use App\Core\Auth\UserPrincipal;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\Paginator;

class BaseService
{

    protected function paginate(Builder $builder, ?int $perPage): array
    {
        if ($perPage === null) $perPage = 15;
        $total = $builder->getCountForPagination();

        $page = Paginator::resolveCurrentPage();
        $lastPage = ceil($total / $perPage);
        if ($page > $lastPage) $page = $lastPage;

        $rows = $total ? $builder->forPage($page, $perPage)->get() : collect();

        return [
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_row' => $total,
            ],
            'rows' => $rows,
        ];
    }

    protected function validateInsert(array $data, UserPrincipal $user)
    {
        $this->validateData($data, null, $user);
    }

    protected function validateUpdate(array $data, ?object $existing, UserPrincipal $user)
    {
        if ($existing == null) {
            abort(404, "No data to update", 'common:error.no-data');
        }
        $this->validateData($data, $existing, $user);
    }

    protected function validateDelete(?object $existing, UserPrincipal $user)
    {
        if ($existing == null) {
            abort(404, "No data to delete", 'common:error.no-data');
        }
        $this->validateData([], $existing, $user);
    }

    protected function validateData(array $data, ?object $existing, UserPrincipal $user)
    {
        if ($existing !== null) {
            $this->validateCompany($existing, $user);
        }
    }

    protected function validateCompany(?object $existing, UserPrincipal $user)
    {
        $companyId = $existing->company_id ?? null;
        if ($companyId === null) {
            abort(400, "Data is read only", 'common:error.data-read-only');
        } else if ($companyId != $user->company_id) {
            abort(400, "You're not belong to this company", 'common:error.invalid-company');
        }
    }

    function getPrimaryKey(): string
    {
        return $this->repo->getPrimaryKey();
    }
}
