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
use App\Modules\CompensationAdmin\Services\SalaryBasisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalaryBasisController extends AbstractController
{
    private SalaryBasisService $svc;

    function __construct(SalaryBasisService $svc)
    {
        $this->svc = $svc;
    }

    /**
     * @OA\Get(
     *     summary="Get all salary basis per page",
     *     path="/salary-basis",
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
        $ret = $this->svc->getPage($this->getPageCriteria($request), current_user(), ['effective' => $effective]);
        return $this->jsonResponse($ret);
    }

    /**
     * @OA\Get(
     *     summary="Get one salary basis",
     *     path="/salary-basis/{salaryBasisId}",
     *
     *     @OA\Parameter(in="path", name="salaryBasisId", required=true, @OA\Schema(type="string")),
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
    function getOne($salaryBasisId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $ret = $this->svc->getOne($salaryBasisId, null, current_user(), ['effective' => $effective]);
        return $this->singleResponse($ret);
    }

    /**
     * @OA\Post(
     *     summary="Insert new salary basis",
     *     path="/salary-basis",
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
     *     summary="Update salary basis",
     *     path="/salary-basis/{salaryBasisId}",
     *
     *     @OA\Parameter(in="path", name="salaryBasisId", required=true, @OA\Schema(type="string")),
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
    function update($salaryBasisId, Request $request): JsonResponse
    {
        $data = $this->getRequestData($request);
        $ret = $this->svc->update($salaryBasisId, $data, current_user());
        return $this->jsonResponse($ret);
    }

    private function getRequestData(Request $request): array
    {
        return $request->validate([
            'salary_basis_code' => 'required|alpha_dash',
            'salary_basis_name' => 'required',
            'element_id' => 'required',
            'input_value_id' => 'required',
            'description' => 'nullable',
        ]);
    }

    /**
     * @OA\Delete(
     *     summary="Delete salary basis",
     *     path="/salary-basis/{salaryBasisId}",
     *
     *     @OA\Parameter(in="path", name="salaryBasisId", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Compensation"},
     * )
     */
    function delete($salaryBasisId): JsonResponse
    {
        $ret = $this->svc->delete($salaryBasisId, current_user());
        return $this->jsonResponse($ret);
    }

}
