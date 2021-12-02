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

use App\Core\Repositories\AbstractRepository;
use App\Modules\MasterData\Models\FormulaList;

class FormulaListRepository extends AbstractRepository
{
    protected FormulaList $model;

    function __construct(FormulaList $model)
    {
        $this->model = $model;
    }

    function allFormulas(string $category): array
    {
        $ret = [];
        $q = $this->model->where('formula_category', $category)->select('formula_name', 'parameters');

        foreach ($q->cursor() as $item) {
            $parameters = array_map(function($p) {
                return (object) [
                    'name' => $p->name,
                    'type' => property_exists($p, 'type') ? $p->type : null,
                    'out' => property_exists($p, 'out') ? $p->out : false,
                ];
            }, json_decode($item->parameters) ?? []);

            $ret[strtolower($item->formula_name)] = (object) [
                'formula_name' => $item->formula_name,
                'params' => $parameters,
            ];
        }
        return $ret;
    }
}
