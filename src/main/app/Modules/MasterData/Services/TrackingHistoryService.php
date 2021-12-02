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

use App\Core\Auth\UserPrincipal;
use App\Core\Services\AbstractService;
use App\Modules\MasterData\Repositories\TrackingHistoryRepository;

class TrackingHistoryService extends AbstractService
{
    private static array $TRACKING_TABLES = [
        'pay-element' => ['element_id', 'pay_element'],
        'pay-input-value' => ['input_value_id', 'pay_input_value'],
        'pay-formula' => ['formula_id', 'pay_formula'],
        'pay-formula-result' => ['result_id', 'pay_formula_result'],
        'pay-group' => ['pay_group_id', 'pay_group'],
        'pay-balance-feed' => ['feed_id', 'pay_balance_feed'],
        'pay-element-link' => ['link_id', 'pay_element_link'],
        'pay-element-link-value' => ['value_id', 'pay_element_link_value'],
        'pay-per-entry' => ['entry_id', 'pay_per_entry'],
        'pay-per-entry-value' => ['value_id', 'pay_per_entry_value'],
    ];

    protected TrackingHistoryRepository $repo;

    function __construct(TrackingHistoryRepository $repo)
    {
        $this->repo = $repo;
    }

    function list(string $name, $id, UserPrincipal $user): array
    {
        $tab = self::$TRACKING_TABLES[$name];
        $tableName = $tab[1];
        $columnId = $tab[0];
        return $this->repo->list($tableName, $columnId, $id, $user->company_id);
    }

}
