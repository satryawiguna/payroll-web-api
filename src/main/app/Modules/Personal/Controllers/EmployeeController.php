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

namespace App\Modules\Personal\Controllers;

use App\Core\Controllers\AbstractController;
use App\Modules\Personal\Services\EmployeeService;
use Illuminate\Http\JsonResponse;

class EmployeeController extends AbstractController
{
    private EmployeeService $svc;

    function __construct(EmployeeService $svc)
    {
        $this->svc = $svc;
    }

    /**
     * @OA\Get(
     *     path="/employees/list-cbx",
     *     summary="Get all employees for combobox",
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Personal"},
     * )
     */
    function listCbx(): JsonResponse
    {
        $ret = $this->svc->listCbx(current_user());
        return $this->jsonResponse(['rows' => $ret]);
    }

}
