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

namespace App\Modules\MasterData\Services;

use App\Core\Services\AbstractService;
use App\Modules\MasterData\Repositories\FormulaListRepository;

class FormulaListService extends AbstractService
{
    protected FormulaListRepository $repo;

    function __construct(FormulaListRepository $repo)
    {
        $this->repo = $repo;
    }

    function listCbx(?string $category): array
    {
        $filters = ($category !== null) ? ['field' => 'formula_category', 'value' => $category] : [];
        $q = $this->repo->getAll(['filters' => $filters]);
        return $q->get()->toArray();
    }

}
