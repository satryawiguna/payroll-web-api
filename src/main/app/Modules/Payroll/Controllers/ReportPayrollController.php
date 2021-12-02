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

namespace App\Modules\Payroll\Controllers;

use App\Core\Controllers\AbstractController;
use App\Modules\Payroll\Services\ReportPayrollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportPayrollController extends AbstractController
{
    private ReportPayrollService $svc;

    function __construct(ReportPayrollService $svc)
    {
        $this->svc = $svc;
    }

    function perPayslip($employeeId, Request $request): JsonResponse
    {
        $periodStart = str_to_date($request->get('period-start')) ?? today()->startOfMonth();
        $periodEnd = str_to_date($request->get('period-end')) ?? today()->lastOfMonth();
        $ret = $this->svc->perPayslip($employeeId, $periodStart, $periodEnd, current_user());
        return $this->jsonResponse($ret);
    }
}
