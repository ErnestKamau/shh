<?php

namespace App\Http\Controllers\Equipment;

use App\Http\Controllers\Controller;
use App\Services\AI\Repository\ReportingMartDashboardService;
use Illuminate\Http\Request;

class EquipmentDashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Show the Equipment Management Dashboard.
     *
     * @param Request $request
     * @param ReportingMartDashboardService $reportingMartDashboardService
     * @return \Illuminate\View\View
     */
    public function index(Request $request, ReportingMartDashboardService $reportingMartDashboardService)
    {
        $equipmentReliabilityBoard = $reportingMartDashboardService->getEquipmentReliabilityBoard();

        return view('layouts.equipment.dashboard', compact('equipmentReliabilityBoard'));
    }
}
