<?php

namespace App\Http\Controllers\Audit;

use App\Http\Controllers\Controller;
use App\Models\Audit\AuditSummaryReport;
use App\Imports\AuditSummaryReportsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class AuditController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('layouts.audit.index');
    }

    public function create()
    {
        $select2 = true;
        return view('layouts.audit.create', compact('select2'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_number' => 'required|string|unique:audit_summary_reports,report_number',
            'revision_number' => 'nullable|string',
            'audit_type' => 'required|string',
            'audit_date_start' => 'required|date',
            'audit_date_end' => 'nullable|date|after_or_equal:audit_date_start',
            'auditor_name' => 'nullable|string',
            'auditee_name' => 'nullable|string',
            'status' => 'required|in:draft,completed,closed',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['company_id'] = getUserCompany() ?? 0;

        if (empty($validated['revision_number'])) {
            $validated['revision_number'] = 'REV. 00';
        }

        $audit = AuditSummaryReport::create($validated);

        return redirect()->route('audit.show', $audit->id)->with('success', 'Audit report created successfully.');
    }

    public function show($id)
    {
        $audit = AuditSummaryReport::with(['findings', 'createdBy', 'updatedBy'])->findOrFail($id);
        return view('layouts.audit.show', compact('audit'));
    }

    public function edit($id)
    {
        $audit = AuditSummaryReport::findOrFail($id);
        return view('layouts.audit.edit', compact('audit'));
    }

    public function update(Request $request, $id)
    {
        $audit = AuditSummaryReport::findOrFail($id);

        $validated = $request->validate([
            'report_number' => 'required|string|unique:audit_summary_reports,report_number,' . $id,
            'revision_number' => 'nullable|string',
            'audit_type' => 'required|string',
            'audit_date_start' => 'required|date',
            'audit_date_end' => 'nullable|date|after_or_equal:audit_date_start',
            'auditor_name' => 'nullable|string',
            'auditee_name' => 'nullable|string',
            'status' => 'required|in:draft,completed,closed',
        ]);

        $validated['updated_by'] = Auth::id();

        $audit->update($validated);

        return redirect()->route('audit.show', $audit->id)->with('success', 'Audit report updated successfully.');
    }

    public function destroy($id)
    {
        $audit = AuditSummaryReport::findOrFail($id);
        $audit->delete();

        return redirect()->route('audit.index')->with('success', 'Audit report deleted successfully.');
    }

    public function generatePdf($id)
    {
        $audit = AuditSummaryReport::with(['findings'])->findOrFail($id);
        $company = \App\Company::find(getUserCompany() ?? 0);

        // Set longer execution time for PDF generation
        ini_set('max_execution_time', 300);
        
        // Get company logo path (same as standard report)
        $path = public_path('images/no-logo.png'); // Default fallback
        if ($company && $company->logo) {
            // If logo starts with /storage/, convert to file system path
            if (strpos($company->logo, '/storage/') === 0) {
                $logoPath = str_replace('/storage/', '', $company->logo);
                
                // Try multiple possible locations (in order of preference)
                $possiblePaths = [
                    storage_path('app/public/' . $logoPath),  // Standard Laravel storage
                    storage_path('app/' . $logoPath),        // Direct storage/app
                    public_path('storage/' . $logoPath),     // Public symlink
                ];
                
                foreach ($possiblePaths as $possiblePath) {
                    if (file_exists($possiblePath)) {
                        $path = $possiblePath;
                        break;
                    }
                }
            } else {
                // If it's a relative path like /images/logo.png, use public_path
                $logoPath = public_path(ltrim($company->logo, '/'));
                if (file_exists($logoPath)) {
                    $path = $logoPath;
                }
            }
        }
        
        // Generate PDF using dompdf
        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
        $pdf->getDomPDF()->set_option("isFontSubsettingEnabled", true);

        // Load the view with data
        $pdf = \PDF::loadView('layouts.audit.pdf.summary_report', compact('audit', 'company', 'path'));
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');

        // Generate filename - sanitize report number to remove invalid characters
        $sanitizedReportNumber = preg_replace('/[\/\\\\]/', '_', $audit->report_number);
        $filename = 'Audit_Summary_Report_' . $sanitizedReportNumber . '_' . date('Y-m-d') . '.pdf';

        // Stream the PDF to browser
        return $pdf->stream($filename);
    }

    public function downloadTemplate()
    {
        $auditType = request()->get('audit_type', 'JSL AUDIT SUMMARY REPORT ISO 17025:2017');
        
        $filename = 'audit_complete_import_template_' . date('Y-m-d') . '.xlsx';
        
        $export = new \App\Exports\AuditImportTemplateExport($auditType);
        
        return Excel::download($export, $filename);
    }

    public function bulkImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,xlsx,xls|max:2048'
        ]);

        try {
            // Check file size and estimated row count
            $file = $request->file('import_file');
            $fileSize = $file->getSize();
            
            // Rough estimate: 1KB per row average
            $estimatedRows = $fileSize / 1024;
            
            if ($estimatedRows > 500) {
                return response()->json([
                    'success' => false,
                    'message' => 'File too large. Maximum 500 rows allowed. Estimated rows: ' . round($estimatedRows)
                ], 413);
            }

            // Check if file has multiple sheets (Excel format)
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
                $sheetCount = $spreadsheet->getSheetCount();
                
                // If Excel file with multiple sheets, use complete import
                if ($sheetCount > 1) {
                    $import = new \App\Imports\AuditCompleteImport();
                    Excel::import($import, $file);
                    
                    $results = $import->getResults();
                    $totalSuccess = $results['success']['audits'] + $results['success']['findings'];
                    $totalFailed = $results['failed']['audits'] + $results['failed']['findings'];
                    
                    if ($totalFailed > 0) {
                        $errorMessage = "Import completed with errors. {$results['success']['audits']} audit report(s) and {$results['success']['findings']} finding(s) imported successfully, {$totalFailed} failed.";
                        
                        session(['import_errors' => $results['errors']]);
                        
                        return response()->json([
                            'success' => false,
                            'message' => $errorMessage,
                            'details' => $results
                        ], 422);
                    }

                    return response()->json([
                        'success' => true,
                        'message' => "Import successful! {$results['success']['audits']} audit report(s) and {$results['success']['findings']} finding(s) have been imported.",
                        'imported_count' => [
                            'audits' => $results['success']['audits'],
                            'findings' => $results['success']['findings']
                        ]
                    ]);
                }
            } catch (\Exception $e) {
                // Not an Excel file or error reading, fall back to CSV/single sheet import
            }

            // For CSV or single sheet Excel, import only audit reports
            $import = new AuditSummaryReportsImport();
            Excel::import($import, $file);
            
            $results = $import->getResults();

            if ($results['failed'] > 0) {
                $errorMessage = "Import completed with errors. {$results['success']} audit report(s) imported successfully, {$results['failed']} failed.";
                
                session(['import_errors' => $results['errors']]);
                
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'details' => $results
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => "Import successful! {$results['success']} audit report(s) have been imported.",
                'imported_count' => $results['success']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadFindingsTemplate($id)
    {
        $audit = AuditSummaryReport::findOrFail($id);

        $filename = 'audit_findings_import_template_' . preg_replace('/[\/\\\\]/', '_', $audit->report_number) . '.xlsx';
        
        $export = new \App\Exports\AuditFindingsTemplateExport();
        
        return Excel::download($export, $filename);
    }

    public function bulkImportFindings(Request $request, $id)
    {
        $audit = AuditSummaryReport::findOrFail($id);

        $request->validate([
            'import_file' => 'required|file|mimes:csv,xlsx,xls|max:2048'
        ]);

        try {
            // Check file size and estimated row count
            $file = $request->file('import_file');
            $fileSize = $file->getSize();
            
            // Rough estimate: 1KB per row average
            $estimatedRows = $fileSize / 1024;
            
            if ($estimatedRows > 500) {
                return response()->json([
                    'success' => false,
                    'message' => 'File too large. Maximum 500 rows allowed. Estimated rows: ' . round($estimatedRows)
                ], 413);
            }

            $import = new \App\Imports\AuditFindingsImport($id);
            Excel::import($import, $file);
            
            $results = $import->getResults();

            if ($results['failed'] > 0) {
                $errorMessage = "Import completed with errors. {$results['success']} finding(s) imported successfully, {$results['failed']} failed.";
                
                // Store errors in session for detailed display
                session(['import_errors' => $results['errors']]);
                
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'details' => $results
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => "Import successful! {$results['success']} finding(s) have been imported.",
                'imported_count' => $results['success']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
