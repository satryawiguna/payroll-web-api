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
use App\Modules\CompensationAdmin\Services\PayrollBalanceFeedService;
use App\Modules\CompensationAdmin\Services\PayrollBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollBalanceController extends AbstractController
{
    private PayrollBalanceService $svc;
    private PayrollBalanceFeedService $feedSvc;

    function __construct(PayrollBalanceService $svc, PayrollBalanceFeedService $feedSvc)
    {
        $this->svc = $svc;
        $this->feedSvc = $feedSvc;
    }

    /**
     * @OA\Get(
     *     summary="Get all payroll balance per page",
     *     path="/payroll-balances",
     *
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
        $res = $this->svc->getPage($this->getPageCriteria($request), current_user());
        return $this->jsonResponse($res);
    }

    /**
     * @OA\Get(
     *     summary="Get one payroll balance",
     *     path="/payroll-balances/{balanceId}",
     *
     *     @OA\Parameter(in="path", name="balanceId", required=true, @OA\Schema(type="string")),
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
    function getOne($balanceId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $ret = $this->svc->getOne($balanceId, null, current_user(), ['effective' => $effective]);
        return $this->singleResponse($ret);
    }

    /**
     * @OA\Post(
     *     summary="Insert new payroll balance",
     *     path="/payroll-balances",
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
     *     summary="Update payroll balance",
     *     path="/payroll-balances/{balanceId}",
     *
     *     @OA\Parameter(in="path", name="balanceId", required=true, @OA\Schema(type="string")),
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
    function update($balanceId, Request $request): JsonResponse
    {
        $data = $this->getRequestData($request);
        $ret = $this->svc->update($balanceId, $data, current_user());
        return $this->jsonResponse($ret);
    }

    private function getRequestData(Request $request): array
    {
        return $request->validate([
            'balance_name' => 'required',
            'balance_feed_type' => 'required',
            'description' => 'nullable',

            'feeds.*.effective_start' => 'nullable|date',
            'feeds.*.effective_end' => 'nullable|date',
            'feeds.*.classification_id' => 'nullable',
            'feeds.*.element_id' => 'nullable',
            'feeds.*.add_subtract' => 'required',
        ]);
    }

    /**
     * @OA\Delete(
     *     summary="Delete payroll balance",
     *     path="/payroll-balances/{balanceId}",
     *
     *     @OA\Parameter(in="path", name="balanceId", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Compensation"},
     * )
     */
    function delete($balanceId): JsonResponse
    {
        $ret = $this->svc->delete($balanceId, current_user());
        return $this->jsonResponse($ret);
    }

    /**
     * @OA\Get(
     *     summary="Get one payroll balance feed",
     *     path="/payroll-elements/values/{feedId}",
     *
     *     @OA\Parameter(in="path", name="feedId", required=true, @OA\Schema(type="string")),
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
    function getOneBalanceFeed($feedId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $ret = $this->feedSvc->getOne($feedId, $effective, null, current_user());
        return $this->singleResponse($ret);
    }

    /**
     * @OA\Post(
     *     summary="Insert new payroll balance feed",
     *     path="/payroll-balances/{balanceId}/feeds",
     *
     *     @OA\Parameter(in="path", name="balanceId", required=true, @OA\Schema(type="string")),
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
    function insertBalanceFeed($balanceId, Request $request): JsonResponse
    {
        $data = $this->getRequestBalanceFeed($request);
        $data['balance_id'] = $balanceId;
        $ret = $this->feedSvc->insert($data, current_user());
        return $this->jsonResponse($ret, 201);
    }

    /**
     * @OA\Put(
     *     summary="Update payroll balance feed",
     *     path="/payroll-balances/feeds/{balanceId}",
     *
     *     @OA\Parameter(in="path", name="balanceId", required=true, @OA\Schema(type="string")),
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
    function updateBalanceFeed($feedId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $changeInsert = $this->isChangeInsert($request);
        $data = $this->getRequestBalanceFeed($request);
        $ret = $this->feedSvc->update($feedId, $effective, $data, current_user(), $changeInsert);
        return $this->jsonResponse($ret);
    }

    private function getRequestBalanceFeed(Request $request): array
    {
        return $request->validate([
            'effective_start' => 'nullable|date',
            'effective_end' => 'nullable|date',
            'classification_id' => 'nullable',
            'element_id' => 'nullable',
            'add_subtract' => 'required',
        ]);
    }

    /**
     * @OA\Delete(
     *     summary="Delete payroll balance feed",
     *     path="/payroll-balances/feeds/{balanceId}",
     *
     *     @OA\Parameter(in="path", name="balanceId", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Compensation"},
     * )
     */
    function deleteBalanceFeed($feedId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request, false);
        $ret = $this->feedSvc->delete($feedId, $effective, current_user());
        return $this->jsonResponse($ret);
    }
}
