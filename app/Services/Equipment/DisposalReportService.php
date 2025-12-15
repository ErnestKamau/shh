<?php

namespace App\Services\Equipment;

use App\Models\Equipments\EquipmentDisposal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class DisposalReportService
{
    /**
     * Generate PDF report for disposal
     *
     * @param EquipmentDisposal $disposal
     * @return string Path to generated PDF file
     */
    public function generateReport(EquipmentDisposal $disposal): string
    {
        // Ensure disposal is fully loaded with relationships
        $disposal->load([
            'equipment',
            'requester',
            'executor',
            'witness',
            'approvals.approver',
            'files',
        ]);

        // Generate SHA-256 hash of the disposal record
        $shaHash = $this->generateShaHash($disposal);

        // Update disposal with hash if not set
        if (!$disposal->sha_hash) {
            $disposal->sha_hash = $shaHash;
            $disposal->save();
        }

        // Get company details
        $company = getActiveCompany();
        $companyLogo = $this->getCompanyLogoPath($company);

        // Get equipment metadata
        $equipment = $disposal->equipment;

        // Get calibration and maintenance history
        $calibrationHistory = $this->getCalibrationHistory($equipment);
        $maintenanceHistory = $this->getMaintenanceHistory($equipment);

        // Prepare data for PDF template
        $data = [
            'disposal' => $disposal,
            'equipment' => $equipment,
            'company' => $company,
            'companyLogo' => $companyLogo,
            'calibrationHistory' => $calibrationHistory,
            'maintenanceHistory' => $maintenanceHistory,
            'approvals' => $disposal->approvals()->with('approver')->orderBy('step')->get(),
            'files' => $disposal->files,
            'shaHash' => $disposal->sha_hash,
            'generatedAt' => now(),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('layouts.equipment.disposal.report', $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->getDomPDF()->set_option('isRemoteEnabled', true);

        // Create directory if it doesn't exist
        $reportDir = storage_path('app/reports/equipment-disposals');
        if (!File::exists($reportDir)) {
            File::makeDirectory($reportDir, 0755, true);
        }

        // Generate filename
        $filename = 'disposal-' . $disposal->id . '-' . date('Y-m-d-His') . '.pdf';
        $filePath = $reportDir . '/' . $filename;

        // Save PDF
        $pdf->save($filePath);

        // Store relative path in database
        $relativePath = '/storage/reports/equipment-disposals/' . $filename;
        $disposal->pdf_report_path = $relativePath;
        $disposal->save();

        return $relativePath;
    }

    /**
     * Generate SHA-256 hash of disposal record for anti-tamper purposes
     *
     * @param EquipmentDisposal $disposal
     * @return string
     */
    public function generateShaHash(EquipmentDisposal $disposal): string
    {
        // Create a string representation of all critical fields
        $hashData = [
            'id' => $disposal->id,
            'equipment_id' => $disposal->equipment_id,
            'justification' => $disposal->justification,
            'proposed_method' => $disposal->proposed_method,
            'risk_level' => $disposal->risk_level,
            'regulatory_category' => $disposal->regulatory_category,
            'requested_by' => $disposal->requested_by,
            'status' => $disposal->status,
            'final_disposal_method' => $disposal->final_disposal_method,
            'disposal_date' => $disposal->disposal_date ? $disposal->disposal_date->format('Y-m-d') : null,
            'executed_by' => $disposal->executed_by,
            'witness_id' => $disposal->witness_id,
            'created_at' => $disposal->created_at ? $disposal->created_at->toIso8601String() : null,
            'updated_at' => $disposal->updated_at ? $disposal->updated_at->toIso8601String() : null,
        ];

        // Include all approval decisions
        $approvals = $disposal->approvals()->orderBy('step')->get();
        foreach ($approvals as $approval) {
            $hashData['approval_' . $approval->step] = [
                'approver_id' => $approval->approver_id,
                'decision' => $approval->decision,
                'decided_at' => $approval->decided_at ? $approval->decided_at->toIso8601String() : null,
            ];
        }

        // Convert to JSON and generate hash
        $jsonString = json_encode($hashData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return hash('sha256', $jsonString);
    }

    /**
     * Get company logo path for PDF
     *
     * @param mixed $company
     * @return string
     */
    protected function getCompanyLogoPath($company): string
    {
        if ($company && $company->report_logo) {
            $logoPath = public_path($company->report_logo);
            if (file_exists($logoPath)) {
                return $logoPath;
            }
        }

        // Fallback to default logo
        $defaultLogo = public_path('images/logo-report.png');
        if (file_exists($defaultLogo)) {
            return $defaultLogo;
        }

        return public_path('images/no-logo.png');
    }

    /**
     * Get calibration history for equipment
     *
     * @param \App\Models\Equipments\Equipment $equipment
     * @return \Illuminate\Support\Collection
     */
    protected function getCalibrationHistory($equipment)
    {
        return \App\Models\Equipments\MaintainanceCalibrationLog::where('equipment_id', $equipment->id)
            ->where('type', 'calibration')
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get maintenance history for equipment
     *
     * @param \App\Models\Equipments\Equipment $equipment
     * @return \Illuminate\Support\Collection
     */
    protected function getMaintenanceHistory($equipment)
    {
        return \App\Models\Equipments\MaintainanceCalibrationLog::where('equipment_id', $equipment->id)
            ->where('type', 'maintainance')
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Download PDF report
     *
     * @param EquipmentDisposal $disposal
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadReport(EquipmentDisposal $disposal)
    {
        // Generate report if it doesn't exist
        if (!$disposal->pdf_report_path || !file_exists(public_path($disposal->pdf_report_path))) {
            $this->generateReport($disposal);
        }

        $filePath = public_path($disposal->pdf_report_path);
        
        if (!file_exists($filePath)) {
            throw new \Exception('PDF report not found.');
        }

        $filename = 'disposal-report-' . $disposal->id . '.pdf';

        return response()->download($filePath, $filename);
    }

    /**
     * Stream PDF report
     *
     * @param EquipmentDisposal $disposal
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function streamReport(EquipmentDisposal $disposal)
    {
        // Generate report if it doesn't exist
        if (!$disposal->pdf_report_path || !file_exists(public_path($disposal->pdf_report_path))) {
            $this->generateReport($disposal);
        }

        $filePath = public_path($disposal->pdf_report_path);
        
        if (!file_exists($filePath)) {
            throw new \Exception('PDF report not found.');
        }

        return response()->file($filePath);
    }
}

