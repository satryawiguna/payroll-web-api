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
use App\Modules\Payroll\Services\PayrollEntryService;
use App\Modules\Payroll\Services\PayrollEntryValueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollEntryController extends AbstractController
{
    private PayrollEntryService $svc;
    private PayrollEntryValueService $valueSvc;

    function __construct(PayrollEntryService $svc, PayrollEntryValueService $valueSvc)
    {
        $this->svc = $svc;
        $this->valueSvc = $valueSvc;
    }

    /**
     * @OA\Get(
     *     summary="Get all employee per page beserta payroll entries nya",
     *     path="/payroll-entries/employees",
     *
     *     @OA\Parameter(in="query", name="effective", @OA\Schema(type="date", format="yyyy-MM-dd")),
     *     @OA\Parameter(in="query", name="page", @OA\Schema(type="number")),
     *     @OA\Parameter(in="query", name="per-page", @OA\Schema(type="number")),
     *     @OA\Parameter(in="query", name="q", @OA\Schema(type="string")),
     *     @OA\Parameter(in="query", name="filters", @OA\Schema(type="{field: string, operator?: string, value: any}[]")),
     *     @OA\Parameter(in="query", name="sorts", @OA\Schema(type="string[]")),
     *     @OA\Parameter(in="query", name="include-entries", @OA\Schema(type="boolean")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Payroll"},
     * )
     */
    function getEmployees(Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $options = [];
        if ($request->get('include-entries') !== null) {
            $options['include-entries'] =  $request->boolean('include-entries');
        }
        $ret = $this->svc->getEmployees($effective, $this->getPageCriteria($request), current_user(), $options);
        return $this->jsonResponse($ret);
    }

    /**
     * @OA\Get(
     *     summary="Get all entries one employee",
     *     path="/payroll-entries/employees/{employeeId}",
     *
     *     @OA\Parameter(in="path", name="employeeId", required=true, @OA\Schema(type="number")),
     *     @OA\Parameter(in="query", name="effective", @OA\Schema(type="date", format="yyyy-MM-dd")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Payroll"},
     * )
     */
    function getEmployee($employeeId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $ret = $this->svc->getEmployee($employeeId, $effective, current_user());
        return $this->singleResponse($ret);
    }

    /**
     * @OA\Get(
     *     summary="Get all entries one employee (hanya entries, info employee tidak termasuk)",
     *     path="/payroll-entries/employees/{employeeId}/entries",
     *
     *     @OA\Parameter(in="path", name="employeeId", required=true, @OA\Schema(type="number")),
     *     @OA\Parameter(in="query", name="effective", @OA\Schema(type="date", format="yyyy-MM-dd")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Payroll"},
     * )
     */
    function getEntries($employeeId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $ret = $this->svc->getEmployee($employeeId, $effective, current_user());
        return $this->singleResponse(['rows' => $ret['entries']]);
    }

    /**
     * @OA\Get(
     *     summary="Get one payroll entry",
     *     path="/payroll-entries/{entryId}",
     *
     *     @OA\Parameter(in="path", name="entryId", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(in="query", name="effective", @OA\Schema(type="date", format="yyyy-MM-dd")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Compensation"},
     * )
     */
    function getOne($entryId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $ret = $this->svc->getOne($entryId, $effective, null, current_user());
        return $this->singleResponse($ret);
    }

    /**
     * @OA\Post(
     *     summary="Insert new payroll entry",
     *     path="/payroll-entries/employees/{employeeId}",
     *
     *     @OA\Parameter(in="path", name="employeeId", required=true, @OA\Schema(type="number")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(mediaType="application/json"),
     *     ),
     *
     *     @OA\Response(response=201, description="Created"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Compensation"},
     * )
     */
    function insert($employeeId, Request $request): JsonResponse
    {
        $data = $this->getRequestInsert($request);
        $data['employee_id'] = $employeeId;
        $ret = $this->svc->insert($data, current_user());
        return $this->jsonResponse($ret, 201);
    }

    private function getRequestInsert(Request $request): array
    {
        return $request->validate([
            'element_id' => 'required',
            'effective_start' => 'nullable|date',
            'effective_end' => 'nullable|date',

            'values' => 'required',
            'values.*.effective_start' => 'nullable|date',
            'values.*.effective_end' => 'nullable|date',
            'values.*.input_value_id' => 'required',
            'values.*.entry_value' => 'nullable',
        ]);
    }

    /**
     * @OA\Put(
     *     summary="Update payroll entry",
     *     path="/payroll-entries/{entryId}",
     *
     *     @OA\Parameter(in="path", name="entryId", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(in="query", name="effective", @OA\Schema(type="date", format="yyyy-MM-dd")),
     *     @OA\Parameter(in="query", name="mode"),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(mediaType="application/json"),
     *     ),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Compensation"},
     * )
     */
    function update($entryId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $changeInsert = $this->isChangeInsert($request);
        $data = $this->getRequestUpdate($request);
        $ret = $this->svc->update($entryId, $effective, $data, current_user(), $changeInsert);
        return $this->jsonResponse($ret);
    }

    private function getRequestUpdate(Request $request): array
    {
        return $request->validate([
            'effective_start' => 'nullable|date',
            'effective_end' => 'nullable|date'
        ]);
    }

    /**
     * @OA\Delete(
     *     summary="Delete payroll entry",
     *     path="/payroll-entries/{entryId}",
     *
     *     @OA\Parameter(in="path", name="entryId", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Compensation"},
     * )
     */
    function delete($entryId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request, false);
        $ret = $this->svc->delete($entryId, $effective, current_user());
        return $this->jsonResponse($ret);
    }

    /**
     * @OA\Get(
     *     summary="Get one payroll entry value",
     *     path="/payroll-entries/values/{valueId}",
     *
     *     @OA\Parameter(in="path", name="valueId", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(in="query", name="effective", @OA\Schema(type="date", format="yyyy-MM-dd")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Compensation"},
     * )
     */
    function getOneValue($valueId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $ret = $this->svc->getOneValue($valueId, $effective, current_user());
        return $this->singleResponse($ret);
    }

    /**
     * @OA\Put(
     *     summary="Update payroll entry value",
     *     path="/payroll-entries/values/{valueId}",
     *
     *     @OA\Parameter(in="path", name="valueId", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(in="query", name="effective", @OA\Schema(type="date", format="yyyy-MM-dd")),
     *     @OA\Parameter(in="query", name="mode"),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(mediaType="application/json"),
     *     ),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Compensation"},
     * )
     */
    function updateValue($valueId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $changeInsert = $this->isChangeInsert($request);
        $data = $this->getRequestUpdateValue($request);
        $ret = $this->valueSvc->update($valueId, $effective, $data, current_user(), $changeInsert);
        return $this->jsonResponse($ret);
    }

    private function getRequestUpdateValue(Request $request): array
    {
        return $request->validate([
            'effective_start' => 'nullable|date',
            'effective_end' => 'nullable|date',
            'entry_value' => 'nullable',
        ]);
    }

}
