<?php

namespace App\Http\Controllers\Equipment;

use App\Http\Controllers\Controller;
use App\Models\Equipments\EquipmentDisposal;
use App\Services\Equipment\DisposalReportService;
use Illuminate\Http\Request;

class DisposalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Download disposal report PDF
     *
     * @param int $disposalId
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadReport(int $disposalId)
    {
        $disposal = EquipmentDisposal::findOrFail($disposalId);
        
        // Check permission
        if (!auth()->user()->check_permission(['equipment', 'disposal', 'report', 'download'])) {
            abort(403, 'You do not have permission to download disposal reports.');
        }

        $reportService = app(DisposalReportService::class);
        
        return $reportService->downloadReport($disposal);
    }

    /**
     * Stream disposal report PDF
     *
     * @param int $disposalId
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function streamReport(int $disposalId)
    {
        $disposal = EquipmentDisposal::findOrFail($disposalId);
        
        // Check permission
        if (!auth()->user()->check_permission(['equipment', 'disposal', 'view'])) {
            abort(403, 'You do not have permission to view disposal reports.');
        }

        $reportService = app(DisposalReportService::class);
        
        return $reportService->streamReport($disposal);
    }
}

