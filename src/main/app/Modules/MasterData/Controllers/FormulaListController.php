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
use App\Modules\MasterData\Services\FormulaListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormulaListController extends AbstractController
{
    private FormulaListService $svc;

    function __construct(FormulaListService $svc)
    {
        $this->svc = $svc;
    }

    /**
     * @OA\Get(
     *     path="/formula-list/list-cbx",
     *     summary="List all predefined formula",
     *
     *     @OA\Parameter(in="query", name="category"),
     *
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *
     *     security={{"bearer_token": {}}},
     *     tags={"Master"},
     * )
     */
    function listCbx(Request $request): JsonResponse
    {
        $category = $request->get('category');
        $res = $this->svc->listCbx($category);
        return $this->jsonResponse(['rows' => $res]);
    }
}
