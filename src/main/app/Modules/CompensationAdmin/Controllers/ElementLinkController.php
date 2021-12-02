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
use App\Modules\CompensationAdmin\Services\ElementLinkService;
use App\Modules\CompensationAdmin\Services\ElementLinkValueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ElementLinkController extends AbstractController
{
    private ElementLinkService $svc;
    private ElementLinkValueService $valueSvc;

    function __construct(ElementLinkService $svc, ElementLinkValueService $valueSvc)
    {
        $this->svc = $svc;
        $this->valueSvc = $valueSvc;
    }

    /**
     * @OA\Get(
     *     summary="Get all element link per page",
     *     path="/element-links",
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
     *     summary="Get one element link",
     *     path="/element-links/{linkId}",
     *
     *     @OA\Parameter(in="path", name="linkId", required=true, @OA\Schema(type="string")),
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
    function getOne($linkId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $ret = $this->svc->getOne($linkId, $effective, null, current_user());
        return $this->singleResponse($ret);
    }

    /**
     * @OA\Post(
     *     summary="Insert new element link",
     *     path="/element-links",
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
    function insert(Request $request): JsonResponse
    {
        $data = $this->getRequestData($request);
        $ret = $this->svc->insert($data, current_user());
        return $this->jsonResponse($ret, 201);
    }

    /**
     * @OA\Put(
     *     summary="Update element link",
     *     path="/element-links/{linkId}",
     *
     *     @OA\Parameter(in="path", name="linkId", required=true, @OA\Schema(type="string")),
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
    function update($linkId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $changeInsert = $this->isChangeInsert($request);
        $data = $this->getRequestData($request);
        $ret = $this->svc->update($linkId, $effective, $data, current_user(), $changeInsert);
        return $this->jsonResponse($ret);
    }

    private function getRequestData(Request $request): array
    {
        return $request->validate([
            'effective_start' => 'nullable|date',
            'effective_end' => 'nullable|date',
            'element_id' => 'required',

            'office_id' => 'nullable|int',
            'location_id' => 'nullable|int',
            'department_id' => 'nullable|int',
            'project_id' => 'nullable|int',
            'position_id' => 'nullable|int',
            'grade_id' => 'nullable|int',
            'pay_group_id' => 'nullable',
            'people_group' => 'nullable',
            'employee_category' => 'nullable',

            'description' => 'nullable',

            'values' => 'nullable',
            'values.*.effective_start' => 'nullable|date',
            'values.*.effective_end' => 'nullable|date',
            'values.*.input_value_id' => 'required',
            'values.*.link_value' => 'required',
            'values.*.description' => 'nullable',
        ]);
    }

    /**
     * @OA\Delete(
     *     summary="Delete element link",
     *     path="/element-links/{linkId}",
     *
     *     @OA\Parameter(in="path", name="linkId", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Compensation"},
     * )
     */
    function delete($linkId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request, false);
        $ret = $this->svc->delete($linkId, $effective, current_user());
        return $this->jsonResponse($ret);
    }

    /**
     * @OA\Get(
     *     summary="Get one element link value",
     *     path="/element-links/values/{valueId}",
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
        $ret = $this->valueSvc->getOne($valueId, $effective, null, current_user());
        return $this->singleResponse($ret);
    }

    /**
     * @OA\Post(
     *     summary="Insert new element link value",
     *     path="/element-links/{linkId}/values",
     *
     *     @OA\Parameter(name="linkId", in="path", required=true, @OA\Schema(type="string")),
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
    function insertValue($linkId, Request $request): JsonResponse
    {
        $data = $this->getRequestValue($request);
        $data['link_id'] = $linkId;
        $ret = $this->valueSvc->insert($data, current_user());
        return $this->jsonResponse($ret, 201);
    }

    /**
     * @OA\Put(
     *     summary="Update element link value",
     *     path="/element-links/values/{valueId}",
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
        $data = $this->getRequestValue($request);
        $ret = $this->valueSvc->update($valueId, $effective, $data, current_user(), $changeInsert);
        return $this->jsonResponse($ret);
    }

    private function getRequestValue(Request $request): array
    {
        return $request->validate([
            'effective_start' => 'nullable|date',
            'effective_end' => 'nullable|date',
            'input_value_id' => 'required',
            'link_value' => 'required',
            'description' => 'nullable',
        ]);
    }

    /**
     * @OA\Delete(
     *     summary="Delete element link value",
     *     path="/element-links/values/{valueId}",
     *
     *     @OA\Parameter(in="path", name="valueId", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Compensation"},
     * )
     */
    function deleteValue($valueId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request, false);
        $ret = $this->valueSvc->delete($valueId, $effective, current_user());
        return $this->jsonResponse($ret);
    }
}
