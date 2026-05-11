<?php

namespace App\Http\Controllers\LivewireControllers;

use App\Http\Controllers\Controller;
use App\SampleType;
use App\AnalysisType;
use App\Models\RemedyHeader;
use App\Models\RatingHeader;

/**
 * LabAppController
 * 
 * Handles all Livewire-based lab management views including:
 * - Sample Types Management
 * - Analytes Management
 * - Remedies Management
 * - Analysis Types Management
 * - Analysis Elements Management
 */
class LabAppController extends Controller
{
    /**
     * Display the sample types management page.
     */
    public function sampleTypes()
    {
        return view('livewire.layout.lab-app', [
            'componentType' => 'sample-types',
            'pageTitle' => 'Sample Types Management'
        ]);
    }

    /**
     * Display the analytes management page.
     */
    public function analytes()
    {
        return view('livewire.layout.lab-app', [
            'componentType' => 'analytes',
            'pageTitle' => 'Analytes Management'
        ]);
    }

    /**
     * Display the remedies management page.
     */
    public function remedies()
    {
        return view('livewire.layout.lab-app', [
            'componentType' => 'remedies',
            'pageTitle' => 'Remedies Management'
        ]);
    }

    /**
     * Display the remedy details management page.
     */
    public function remedyDetails($remedyHeaderId)
    {
        $remedyHeader = RemedyHeader::findOrFail($remedyHeaderId);

        return view('livewire.layout.lab-app', [
            'componentType' => 'remedy-details',
            'pageTitle' => 'Remedy Details - ' . $remedyHeader->name,
            'remedyHeader' => $remedyHeader,
            'remedyHeaderId' => $remedyHeaderId
        ]);
    }

    /**
     * Display the analysis types management page.
     */
    public function analysisTypes($sampleTypeId)
    {
        $sampleType = SampleType::findOrFail($sampleTypeId);

        return view('livewire.layout.lab-app', [
            'componentType' => 'analysis-types',
            'pageTitle' => 'Analysis Types - ' . $sampleType->name,
            'sampleType' => $sampleType
        ]);
    }

    /**
     * Display the analysis elements management page.
     */
    public function elements($analysisTypeId)
    {
        $analysisType = AnalysisType::with('sample_type')->findOrFail($analysisTypeId);

        return view('livewire.layout.lab-app', [
            'componentType' => 'elements',
            'pageTitle' => 'Analysis Elements - ' . $analysisType->name,
            'analysisType' => $analysisType
        ]);
    }

    /**
     * Display the rating hub management page.
     */
    public function ratingHub()
    {
        return view('livewire.layout.lab-app', [
            'componentType' => 'ratings',
            'pageTitle' => 'Rating Hub - Rating Headers Management'
        ]);
    }

    /**
     * Display the rating details management page.
     */
    public function ratingDetails($ratingHeaderId)
    {
        $ratingHeader = RatingHeader::findOrFail($ratingHeaderId);

        return view('livewire.layout.lab-app', [
            'componentType' => 'rating-details',
            'pageTitle' => 'Rating Hub - ' . $ratingHeader->name . ' Details',
            'ratingHeader' => $ratingHeader,
            'ratingHeaderId' => $ratingHeaderId
        ]);
    }

    /**
     * Display the report formats management page.
     */
    public function reportFormats()
    {
        return view('livewire.layout.lab-app', [
            'componentType' => 'report-formats',
            'pageTitle' => 'Report Formats Management'
        ]);
    }

    /**
     * Display the workflow approval configuration page.
     */
    public function workflowApprovals()
    {
        return view('livewire.layout.lab-app', [
            'componentType' => 'workflow-approvals',
            'pageTitle' => 'Checklist Approval Configuration'
        ]);
    }

    /**
     * Display the standard manager page.
     */
    public function standardManager()
    {
        return view('livewire.layout.lab-app', [
            'componentType' => 'standard-manager',
            'pageTitle' => 'Standard Manager'
        ]);
    }

    /**
     * Display the dedicated Report Formats Builder page.
     */
    public function reportFormatBuilder($reportFormatId)
    {
        $reportFormat = \App\ReportFormat::findOrFail($reportFormatId);

        return view('livewire.layout.lab-app', [
            'componentType' => 'report-format-builder',
            'pageTitle' => 'Report Format Builder - ' . $reportFormat->report_name,
            'reportFormatId' => $reportFormatId
        ]);
    }

    /**
     * Display the Labs Management page.
     */
    public function labManager()
    {
        return view('livewire.lab.lab-manager-page');
    }

    /**
     * Display the Monitoring module dashboard.
     */
    public function monitoring()
    {
        return view('livewire.layout.lab-app', [
            'componentType' => 'monitoring',
            'pageTitle' => 'Monitoring',
        ]);
    }

    /**
     * Display the create monitoring template page.
     */
    public function createMonitoringTemplate()
    {
        return view('livewire.layout.lab-app', [
            'componentType' => 'template-create',
            'pageTitle' => 'Create Monitoring Template',
            'breadcrumbItems' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Monitoring', 'url' => route('livewire.monitoring')],
                ['label' => 'Create Template', 'current' => true],
            ],
        ]);
    }
}
