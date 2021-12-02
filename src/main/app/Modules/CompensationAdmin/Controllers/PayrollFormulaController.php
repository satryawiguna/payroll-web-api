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
use App\Modules\CompensationAdmin\Services\PayrollFormulaResultService;
use App\Modules\CompensationAdmin\Services\PayrollFormulaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollFormulaController extends AbstractController
{
    private PayrollFormulaService $svc;
    private PayrollFormulaResultService $resultSvc;

    function __construct(PayrollFormulaService $svc, PayrollFormulaResultService $resultSvc)
    {
        $this->svc = $svc;
        $this->resultSvc = $resultSvc;
    }

    /**
     * @OA\Get(
     *     summary="Get all payroll formula per page",
     *     path="/payroll-formulas",
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
        $res = $this->svc->getPage($effective, $this->getPageCriteria($request), current_user());
        return $this->jsonResponse($res);
    }

    /**
     * @OA\Get(
     *     summary="List all payroll formula for combob box",
     *     path="/payroll-formulas/list-cbx",
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
     *     summary="Get one payroll formula",
     *     path="/payroll-formulas/{formulaId}",
     *
     *     @OA\Parameter(in="path", name="formulaId", required=true, @OA\Schema(type="string")),
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
    function getOne($formulaId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $ret = $this->svc->getOne($formulaId, $effective, null, current_user());
        return $this->singleResponse($ret);
    }

    /**
     * @OA\Post(
     *     summary="Insert new payroll formula",
     *     path="/payroll-formulas",
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
     *     summary="Update payroll formula",
     *     path="/payroll-formulas/{formulaId}",
     *
     *     @OA\Parameter(in="path", name="formulaId", required=true, @OA\Schema(type="string")),
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
    function update($formulaId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $changeInsert = $this->isChangeInsert($request);
        $data = $this->getRequestData($request);
        $ret = $this->svc->update($formulaId, $effective, $data, current_user(), $changeInsert);
        return $this->jsonResponse($ret);
    }

    private function getRequestData(Request $request): array
    {
        return $request->validate([
            'effective_start' => 'nullable|date',
            'effective_end' => 'nullable|date',
            'formula_name' => 'required',
            'element_id' => 'required',
            'formula_type' => 'required',
            'formula_def' => 'nullable',
            'description' => 'nullable',

            'results.*.effective_start' => 'nullable|date',
            'results.*.effective_end' => 'nullable|date',
            'results.*.result_code' => 'required|alpha_dash',
            'results.*.element_id' => 'required',
            'results.*.input_value_id' => 'required',
            'results.*.formula_expr' => 'nullable',
        ]);
    }

    /**
     * @OA\Delete(
     *     summary="Delete payroll formula",
     *     path="/payroll-formulas/{formulaId}",
     *
     *     @OA\Parameter(in="path", name="formulaId", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Compensation"},
     * )
     */
    function delete($formulaId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request, false);
        $ret = $this->svc->delete($formulaId, $effective, current_user());
        return $this->jsonResponse($ret);
    }

    /**
     * @OA\Get(
     *     summary="Get one payroll formula result",
     *     path="/payroll-elements/values/{resultId}",
     *
     *     @OA\Parameter(in="path", name="resultId", required=true, @OA\Schema(type="string")),
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
    function getOneFormulaResult($resultId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $ret = $this->resultSvc->getOne($resultId, $effective, null, current_user());
        return $this->singleResponse($ret);
    }

    /**
     * @OA\Post(
     *     summary="Insert new payroll formula result",
     *     path="/payroll-formulas/{formulaId}/results",
     *
     *     @OA\Parameter(in="path", name="formulaId", required=true, @OA\Schema(type="string")),
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
    function insertFormulaResult($formulaId, Request $request): JsonResponse
    {
        $data = $this->getRequestFormulaResult($request);
        $data['formula_id'] = $formulaId;
        $ret = $this->resultSvc->insert($data, current_user());
        return $this->jsonResponse($ret, 201);
    }

    /**
     * @OA\Put(
     *     summary="Update payroll formula result",
     *     path="/payroll-formulas/results/{resultId}",
     *
     *     @OA\Parameter(in="path", name="resultId", required=true, @OA\Schema(type="string")),
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
    function updateFormulaResult($resultId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request);
        $changeInsert = $this->isChangeInsert($request);
        $data = $this->getRequestFormulaResult($request);
        $ret = $this->resultSvc->update($resultId, $effective, $data, current_user(), $changeInsert);
        return $this->jsonResponse($ret);
    }

    private function getRequestFormulaResult(Request $request): array
    {
        return $request->validate([
            'effective_start' => 'nullable|date',
            'effective_end' => 'nullable|date',
            'result_code' => 'required|alpha_dash',
            'element_id' => 'required',
            'input_value_id' => 'required',
            'formula_expr' => 'nullable',
        ]);
    }

    /**
     * @OA\Delete(
     *     summary="Delete payroll formula result",
     *     path="/payroll-formulas/results/{resultId}",
     *
     *     @OA\Parameter(in="path", name="resultId", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Compensation"},
     * )
     */
    function deleteFormulaResult($resultId, Request $request): JsonResponse
    {
        $effective = $this->getEffectiveDate($request, false);
        $ret = $this->resultSvc->delete($resultId, $effective, current_user());
        return $this->jsonResponse($ret);
    }
}
