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

namespace App\Modules\MasterData\Repositories;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class TrackingHistoryRepository
{
    function list(string $tableName, string $columnId, $id, $companyId): array
    {
        return DB::table($tableName)
            ->select('effective_start', 'effective_end')
            ->where($columnId, $id)
            ->where(function(Builder $q) use ($companyId) {
                $q->orWhereNull('company_id');
                $q->orWhere('company_id', $companyId);
            })
            ->orderBy('effective_start')
            ->get()->toArray();
    }
}
