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

namespace App\Core\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

abstract class AbstractController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function singleResponse($data, $status = null): JsonResponse
    {
        if ($data === null) abort(404, "No data");
        return $this->jsonResponse($data, $status);
    }

    protected function jsonResponse($data, $status = null): JsonResponse
    {
        return response()->json($data, $status ?? 200, []);
    }

    protected function getEffectiveDate(Request $request, bool $useDefault = true): ?Carbon
    {
        $sEffective = $request->get('effective');
        if ($sEffective === null && $useDefault) return Carbon::today();
        return str_to_date($sEffective);
    }

    protected function getPageCriteria(Request $request): array
    {
        return [
            'per_page' => $this->getPerPage($request),
            'search' => $this->getSearchString($request),
            'filters' => $this->getFilters($request),
            'sorts' => $this->getSorts($request),
        ];
    }

    protected function getListCriteria(Request $request): array
    {
        return [
            'search' => $this->getSearchString($request),
            'filters' => $this->getFilters($request),
            'sorts' => $this->getSorts($request),
        ];
    }

    private function getPerPage(Request $request): int
    {
        $v = $request->get('per-page');
        if (empty($v)) return DEFAULT_PER_PAGE;
        return +$v;
    }

    private function getSearchString(Request $request): ?string
    {
        $v = $request->get('q');
        if (empty($v)) return null;
        return $v;
    }

    private function getFilters(Request $request): array
    {
        $v = $request->get('filters');
        if (empty($v)) return [];
        return json_decode($v, true);
    }

    private function getSorts(Request $request): array
    {
        $v = $request->get('sorts');
        if (empty($v)) return [];
        $a = explode(',', $v);
        return array_map(function($d) {
            return trim($d);
        }, $a);
    }

    protected function isChangeInsert(Request $request): bool
    {
        return $request->query('mode') === 'change-insert';
    }
}
