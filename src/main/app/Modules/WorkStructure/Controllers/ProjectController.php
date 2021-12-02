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
use App\Modules\WorkStructure\Services\ProjectService;
use Illuminate\Http\JsonResponse;

class ProjectController extends AbstractController
{
    private ProjectService $svc;

    function __construct(ProjectService $svc)
    {
        $this->svc = $svc;
    }

    /**
     * @OA\Get(
     *     path="/projects/list-cbx",
     *     summary="Get all projects for combobox",
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
