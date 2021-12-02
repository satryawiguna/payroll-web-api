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
use App\Modules\CompensationAdmin\Services\PayrollGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollGroupController extends AbstractController
{
    private PayrollGroupService $svc;

    function __construct(PayrollGroupService $svc)
    {
        $this->svc = $svc;
    }

    /**
     * @OA\Get(
     *     summary="Get all payroll group per page",
     *     path="/payroll-groups",
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
     *     summary="List all payroll groups for combob box",
     *     path="/payroll-groups/list-cbx",
     *
     *     @OA\Parameter(in="query", name="effective", @OA\Schema(type="date", format="yyyy-MM-dd")),
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
        $ret = $this->svc->listCbx($effective, current_user());
        return $this->jsonResponse(['rows' => $ret]);
    }

    /**
     * @OA\Get(
     *     summary="Get one payroll group",
     *     path="/payroll-groups/{payGroupId}",
     *
     *     @OA\Parameter(in="path", name="payGroupId", required=true, @OA\Schema(type="string")),
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
    function getOne($payGroupId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $ret = $this->svc->getOne($payGroupId, $effective, null, current_user());
        return $this->singleResponse($ret);
    }

    /**
     * @OA\Post(
     *     summary="Insert new payroll group",
     *     path="/payroll-groups",
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
     *     summary="Update payroll group",
     *     path="/payroll-groups/{payGroupId}",
     *
     *     @OA\Parameter(in="path", name="payGroupId", required=true, @OA\Schema(type="string")),
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
    function update($payGroupId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $changeInsert = $this->isChangeInsert($request);
        $data = $this->getRequestData($request);
        $ret = $this->svc->update($payGroupId, $effective, $data, current_user(), $changeInsert);
        return $this->jsonResponse($ret);
    }

    private function getRequestData(Request $request): array
    {
        return $request->validate([
            'effective_start' => 'nullable|date',
            'effective_end' => 'nullable|date',
            'pay_group_name' => 'required',
            'description' => 'nullable',
        ]);
    }

    /**
     * @OA\Delete(
     *     summary="Delete payroll group",
     *     path="/payroll-groups/{payGroupId}",
     *
     *     @OA\Parameter(in="path", name="payGroupId", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Compensation"},
     * )
     */
    function delete($payGroupId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request, false);
        $ret = $this->svc->delete($payGroupId, $effective, current_user());
        return $this->jsonResponse($ret);
    }

}
