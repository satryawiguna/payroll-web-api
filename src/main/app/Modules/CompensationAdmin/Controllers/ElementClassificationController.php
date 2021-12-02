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
use App\Modules\CompensationAdmin\Services\ElementClassificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ElementClassificationController extends AbstractController
{
    private ElementClassificationService $svc;

    function __construct(ElementClassificationService $svc)
    {
        $this->svc = $svc;
    }

    /**
     * @OA\Get(
     *     summary="Get all element classification per page",
     *     path="/element-classifications",
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
        $ret = $this->svc->getPage($this->getPageCriteria($request), current_user());
        return $this->jsonResponse($ret);
    }

    /**
     * @OA\Get(
     *     path="/element-classifications/list-cbx",
     *     summary="Get all element classification for combobox",
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Compensation"},
     * )
     */
    function listCbx(): JsonResponse
    {
        $ret = $this->svc->listCbx(current_user());
        return $this->jsonResponse(['rows' => $ret]);
    }

    /**
     * @OA\Get(
     *     summary="Get one element classification",
     *     path="/element-classifications/{classificationId}",
     *
     *     @OA\Parameter(in="path", name="classificationId", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Compensation"},
     * )
     */
    function getOne($classificationId): JsonResponse
    {
        $ret = $this->svc->getOne($classificationId, null, current_user());
        return $this->singleResponse($ret);
    }

    /**
     * @OA\Post(
     *     summary="Insert new element classification",
     *     path="/element-classifications",
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
     *     summary="Update element classification",
     *     path="/element-classifications/{classificationId}",
     *
     *     @OA\Parameter(in="path", name="classificationId", required=true, @OA\Schema(type="string")),
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
    function update($classificationId, Request $request): JsonResponse
    {
        $data = $this->getRequestData($request);
        $ret = $this->svc->update($classificationId, $data, current_user());
        return $this->jsonResponse($ret);
    }

    private function getRequestData(Request $request): array
    {
        return $request->validate([
            'classification_name' => 'required',
            'default_priority' => 'required|integer',
            'description' => 'nullable',
        ]);
    }

    /**
     * @OA\Delete(
     *     summary="Delete element classification",
     *     path="/element-classifications/{classificationId}",
     *
     *     @OA\Parameter(in="path", name="classificationId", required=true, @OA\Schema(type="string")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Compensation"},
     * )
     */
    function delete($classificationId): JsonResponse
    {
        $ret = $this->svc->delete($classificationId, current_user());
        return $this->jsonResponse($ret);
    }

}
