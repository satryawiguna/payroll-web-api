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

namespace App\Modules\MasterData\Controllers;

use App\Core\Controllers\AbstractController;
use App\Modules\MasterData\Services\TrackingHistoryService;
use Illuminate\Http\JsonResponse;

class TrackingHistoryController extends AbstractController
{
    private TrackingHistoryService $svc;

    function __construct(TrackingHistoryService $svc)
    {
        $this->svc = $svc;
    }

    /**
     * @OA\Get(
     *     summary="List date tracking history per item",
     *     path="/tracking-history/{name}/{id}",
     *
     *     @OA\Parameter(in="path", name="name", required=true),
     *     @OA\Parameter(in="path", name="id", required=true, @OA\Schema(type="number | string")),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Master"},
     * )
     */
    function list(string $name, $id): JsonResponse
    {
        $res = $this->svc->list($name, $id, current_user());
        return $this->jsonResponse(['rows' => $res]);
    }
}
