<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardSummaryService $dashboardSummaryService,
    ) {}

    public function summary(Request $request): JsonResponse
    {
        return response()->json($this->dashboardSummaryService->summary($request));
    }

    public function filters(): JsonResponse
    {
        return response()->json($this->dashboardSummaryService->filterOptions());
    }
}
