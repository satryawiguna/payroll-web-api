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
use App\Jobs\PayrollCalculateJob;
use App\Modules\Payroll\Services\PayrollEntryService;
use App\Modules\Payroll\Services\PayrollProcessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollProcessController extends AbstractController
{
    private PayrollProcessService $svc;
    private PayrollEntryService $entrySvc;

    function __construct(PayrollProcessService $svc, PayrollEntryService $entrySvc)
    {
        $this->svc = $svc;
        $this->entrySvc = $entrySvc;
    }

    function getNewProcess(Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $criteria = $this->getPageCriteria($request);
        $options = ['process-type' => PROCESS_TYPE_PAYROLL];
        $res = $this->entrySvc->getEmployees($effective, $criteria, current_user(), $options);
        return $this->jsonResponse($res);
    }

    function insertNewProcess(Request $request): JsonResponse
    {
        $data = $this->getRequestData($request);
        $processId = $this->svc->insertNewProcess(PROCESS_TYPE_PAYROLL, $data, current_user());
        return $this->jsonResponse(['process_id' => $processId]);
    }

    function getNewRetroPay(Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $criteria = $this->getPageCriteria($request);
        $options = ['process-type' => PROCESS_TYPE_RETROPAY];
        $res = $this->entrySvc->getEmployees($effective, $criteria, current_user(), $options);
        return $this->jsonResponse($res);
    }

    function insertNewRetroPay(Request $request): JsonResponse
    {
        $data = $this->getRequestData($request);
        $processId = $this->svc->insertNewProcess(PROCESS_TYPE_RETROPAY, $data, current_user());
        return $this->jsonResponse(['process_id' => $processId]);
    }

    function calculatePayroll($processId): JsonResponse
    {
        PayrollCalculateJob::dispatch(['process_id' => $processId, 'user' => current_user()]);
        return $this->jsonResponse(['status' => 'processing']);
    }

    function validateProcessed($processId): JsonResponse
    {
        $this->svc->validateProcessed($processId, current_user());
        return $this->jsonResponse(['status' => 'validated']);
    }

    function deletePayroll($processId): JsonResponse
    {
        $ret = $this->svc->delete($processId, current_user());
        return $this->jsonResponse($ret);
    }

    private function getRequestData(Request $request): array
    {
        return $request->validate([
            'batch_name' => 'required',
            'process_date' => 'required|date',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'ret_entry_period_start' => 'nullable|date',
            'ret_entry_period_end' => 'nullable|date',
            'description' => 'nullable',

            'office_id' => 'nullable|integer',
            'location_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'project_id' => 'nullable|integer',
            'position_id' => 'nullable|integer',
            'grade_id' => 'nullable|integer',
            'pay_group_id' => 'nullable',
            'people_group' => 'nullable',
            'employee_category' => 'nullable|alpha_dash',
            'employee_id' => 'nullable',

            'items' => 'required',
            'items.*.employee_id' => 'required',
            'items.*.entries' => 'required',
            'items.*.entries.*.element_id' => 'required',
            'items.*.entries.*.values.*.input_value_id' => 'required',
        ]);
    }
}
