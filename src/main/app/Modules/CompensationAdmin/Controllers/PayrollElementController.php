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

namespace App\Modules\CompensationAdmin\Controllers;

use App\Core\Controllers\AbstractController;
use App\Modules\CompensationAdmin\Services\PayrollElementService;
use App\Modules\CompensationAdmin\Services\PayrollInputValueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollElementController extends AbstractController
{
    private PayrollElementService $svc;
    private PayrollInputValueService $inputValueSvc;

    function __construct(PayrollElementService $svc, PayrollInputValueService $inputValueSvc)
    {
        $this->svc = $svc;
        $this->inputValueSvc = $inputValueSvc;
    }

    /**
     * @OA\Get(
     *     summary="Get all payroll element per page",
     *     path="/payroll-elements",
     *
     *     @OA\Parameter(in="query", name="effective", @OA\Schema(type="date", format="yyyy-MM-dd")),
     *     @OA\Parameter(in="query", name="page", @OA\Schema(type="number")),
     *     @OA\Parameter(in="query", name="per-page", @OA\Schema(type="number")),
     *     @OA\Parameter(in="query", name="q", @OA\Schema(type="string")),
     *     @OA\Parameter(in="query", name="filters", @OA\Schema(type="{field: string, operator?: string, value: any}[]")),
     *     @OA\Parameter(in="query", name="sorts", @OA\Schema(type="string[]")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Compensation"},
     * )
     */
    function getPage(Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $ret = $this->svc->getPage($effective, $this->getPageCriteria($request), current_user());
        return $this->jsonResponse($ret);
    }

    /**
     * @OA\Get(
     *     summary="Get all payroll element for combobox",
     *     path="/payroll-elements/list-cbx",
     *
     *     @OA\Parameter(in="query", name="effective", @OA\Schema(type="date", format="yyyy-MM-dd")),
     *     @OA\Parameter(in="query", name="q", @OA\Schema(type="string")),
     *     @OA\Parameter(in="query", name="filters", @OA\Schema(type="{field: string, operator?: string, value: any}[]")),
     *     @OA\Parameter(in="query", name="sorts", @OA\Schema(type="string[]")),
     *     @OA\Parameter(in="query", name="include-values", @OA\Schema(type="boolean")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Compensation"},
     * )
     */
    function listCbx(Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $criteria = $this->getListCriteria($request);
        $options = ['include-values' => $request->boolean('include-values')];
        $ret = $this->svc->listCbx($effective, $criteria, current_user(), $options);
        return $this->jsonResponse(['rows' => $ret]);
    }

    /**
     * @OA\Get(
     *     summary="Get one payroll element",
     *     path="/payroll-elements/{elementId}",
     *
     *     @OA\Parameter(in="path", name="elementId", required=true, @OA\Schema(type="string")),
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
    function getOne($elementId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $ret = $this->svc->getOne($elementId, $effective, null, current_user());
        return $this->singleResponse($ret);
    }

    /**
     * @OA\Post(
     *     summary="Insert new payroll element",
     *     path="/payroll-elements",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(mediaType="application/json", @OA\Schema(
     *             @OA\Property(property="element_name", type="string"),
     *             @OA\Property(property="effective_start", type="string", format="yyyy-MM-dd"),
     *             @OA\Property(property="effective_end", type="string", format="yyyy-MM-dd"),
     *         )),
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
    function insert(Request $request): JsonResponse
    {
        $data = $this->getRequestData($request);
        $ret = $this->svc->insert($data, current_user());
        return $this->jsonResponse($ret, 201);
    }

    /**
     * @OA\Put(
     *     summary="Update payroll element",
     *     path="/payroll-elements/{elementId}",
     *
     *     @OA\Parameter(in="path", name="elementId", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(in="query", name="effective", @OA\Schema(type="date", format="yyyy-MM-dd")),
     *     @OA\Parameter(in="query", name="mode"),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(mediaType="application/json", @OA\Schema(
     *             @OA\Property(property="element_name", type="string"),
     *             @OA\Property(property="effective_start", type="string", format="yyyy-MM-dd"),
     *             @OA\Property(property="effective_end", type="string", format="yyyy-MM-dd"),
     *         )),
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
    function update($elementId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $changeInsert = $this->isChangeInsert($request);
        $data = $this->getRequestData($request);
        $ret = $this->svc->update($elementId, $effective, $data, current_user(), $changeInsert);
        return $this->jsonResponse($ret);
    }

    private function getRequestData(Request $request): array
    {
        return $request->validate([
            'effective_start' => 'nullable|date',
            'effective_end' => 'nullable|date',
            'element_code' => 'required|alpha_dash',
            'element_name' => 'required',
            'classification_id' => 'required',
            'processing_priority' => 'required|integer',
            'retro_element_id' => 'nullable',
            'is_recurring' => 'required|boolean',
            'is_once_per_period' => 'required|boolean',
            'description' => 'nullable',

            'values.*.effective_start' => 'nullable|date',
            'values.*.effective_end' => 'nullable|date',
            'values.*.value_code' => 'required|alpha_dash',
            'values.*.value_name' => 'required',
            'values.*.data_type' => 'required',
            'values.*.default_value' => 'nullable',
            'values.*.description' => 'nullable',
        ]);
    }

    /**
     * @OA\Delete(
     *     summary="Delete payroll element",
     *     path="/payroll-elements/{elementId}",
     *
     *     @OA\Parameter(in="path", name="elementId", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Compensation"},
     * )
     */
    function delete($elementId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request, false);
        $ret = $this->svc->delete($elementId, $effective, current_user());
        return $this->jsonResponse($ret);
    }

    /**
     * @OA\Get(
     *     summary="Get one payroll input value",
     *     path="/payroll-elements/values/{inputValueId}",
     *
     *     @OA\Parameter(in="path", name="inputValueId", required=true, @OA\Schema(type="string")),
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
    function getOneInputValue($inputValueId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $ret = $this->inputValueSvc->getOne($inputValueId, $effective, null, current_user());
        return $this->singleResponse($ret);
    }

    /**
     * @OA\Post(
     *     summary="Insert new payroll input value",
     *     path="/payroll-elements/{elementId}/values",
     *
     *     @OA\Parameter(name="elementId", in="path", required=true, @OA\Schema(type="string")),
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
    function insertInputValue($elementId, Request $request): JsonResponse
    {
        $data = $this->getRequestInputValue($request);
        $data['element_id'] = $elementId;
        $ret = $this->inputValueSvc->insert($data, current_user());
        return $this->jsonResponse($ret, 201);
    }

    /**
     * @OA\Put(
     *     summary="Update payroll input value",
     *     path="/payroll-elements/values/{inputValueId}",
     *
     *     @OA\Parameter(in="path", name="inputValueId", required=true, @OA\Schema(type="string")),
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
    function updateInputValue($inputValueId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $changeInsert = $this->isChangeInsert($request);
        $data = $this->getRequestInputValue($request);
        $ret = $this->inputValueSvc->update($inputValueId, $effective, $data, current_user(), $changeInsert);
        return $this->jsonResponse($ret);
    }

    private function getRequestInputValue(Request $request): array
    {
        return $request->validate([
            'effective_start' => 'nullable|date',
            'effective_end' => 'nullable|date',
            'value_code' => 'required|alpha_dash',
            'value_name' => 'required',
            'data_type' => 'required',
            'default_value' => 'nullable',
            'description' => 'nullable',
        ]);
    }

    /**
     * @OA\Delete(
     *     summary="Delete payroll input value",
     *     path="/payroll-elements/values/{inputValueId}",
     *
     *     @OA\Parameter(in="path", name="inputValueId", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Compensation"},
     * )
     */
    function deleteInputValue($inputValueId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request, false);
        $ret = $this->inputValueSvc->delete($inputValueId, $effective, current_user());
        return $this->jsonResponse($ret);
    }
}
