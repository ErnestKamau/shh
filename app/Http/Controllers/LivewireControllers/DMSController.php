<?php

namespace App\Http\Controllers\LivewireControllers;

use App\Http\Controllers\Controller;

/**
 * DMSController
 * 
 * Handles all Livewire-based Document Management System views
 */
class DMSController extends Controller
{
    /**
     * Display the DMS dashboard
     */
    public function dashboard()
    {
        return view('livewire.layout.dms-app', [
            'componentType' => 'dashboard',
            'pageTitle' => 'Document Management System - Dashboard'
        ]);
    }

    /**
     * Display the document types management page
     */
    public function documentTypes()
    {
        return view('livewire.layout.dms-app', [
            'componentType' => 'document-types',
            'pageTitle' => 'Document Management System - Document Types'
        ]);
    }

    /**
     * Display the active documents page
     */
    public function activeDocuments()
    {
        return view('livewire.layout.dms-app', [
            'componentType' => 'active-documents',
            'pageTitle' => 'Document Management System - Active Documents'
        ]);
    }

    /**
     * Display the archived documents page
     */
    public function archivedDocuments()
    {
        return view('livewire.layout.dms-app', [
            'componentType' => 'archived-documents',
            'pageTitle' => 'Document Management System - Archived Documents'
        ]);
    }

    /**
     * Display the amendments management page
     */
    public function amendments()
    {
        return view('livewire.layout.dms-app', [
            'componentType' => 'amendments',
            'pageTitle' => 'Document Management System - Amendments'
        ]);
    }

    /**
     * Display the reports page
     */
    public function reports()
    {
        return view('livewire.layout.dms-app', [
            'componentType' => 'reports',
            'pageTitle' => 'Document Management System - Reports'
        ]);
    }
}

