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

        $this->authorize('view', $disposal);

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

        $this->authorize('view', $disposal);

        $reportService = app(DisposalReportService::class);
        
        return $reportService->streamReport($disposal);
    }
}

