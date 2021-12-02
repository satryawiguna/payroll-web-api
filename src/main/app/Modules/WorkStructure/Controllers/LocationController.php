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

namespace App\Modules\WorkStructure\Controllers;

use App\Core\Controllers\AbstractController;
use App\Modules\WorkStructure\Services\LocationService;
use Illuminate\Http\JsonResponse;

class LocationController extends AbstractController
{
    private LocationService $svc;

    function __construct(LocationService $svc)
    {
        $this->svc = $svc;
    }

    /**
     * @OA\Get(
     *     path="/locations/list-cbx",
     *     summary="Get all locations/work areas for combobox",
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"WorkStructure"},
     * )
     */
    function listCbx(): JsonResponse
    {
        $ret = $this->svc->listCbx(current_user());
        return $this->jsonResponse(['rows' => $ret]);
    }

}
