<?php

namespace App\Http\Controllers\LivewireControllers;

use App\Http\Controllers\Controller;
use App\Models\CRM\CRMCustomer;

/**
 * CRMAppController
 * 
 * Handles all Livewire-based CRM management views including:
 * - Customer Management
 * - Customer Profile Management
 * - Company Units Management
 * - Sample Points Management
 * - Contacts Management
 * - Reports Viewing
 * - Amendment Viewing
 */
class CRMAppController extends Controller
{
    /**
     * Display the customer management page.
     */
    public function customers()
    {
        return view('livewire.layout.crm-app', [
            'componentType' => 'customers',
            'pageTitle' => 'Customer Management'
        ]);
    }

    /**
     * Display the customer profile page.
     */
    public function customerProfile($customerId)
    {
        $customer = CRMCustomer::findOrFail($customerId);
        
        return view('livewire.layout.crm-app', [
            'componentType' => 'customer-profile',
            'pageTitle' => 'Customer Profile - ' . $customer->name,
            'customer' => $customer,
            'customerId' => $customerId
        ]);
    }

    /**
     * Display the sample points management page.
     */
    public function samplePoints()
    {
        return view('livewire.layout.crm-app', [
            'componentType' => 'sample-points',
            'pageTitle' => 'Sample Points Management'
        ]);
    }

    /**
     * Display the areas management page.
     */
    public function areas()
    {
        return view('livewire.layout.crm-app', [
            'componentType' => 'areas',
            'pageTitle' => 'Areas Management'
        ]);
    }
    /**
     * Display the complaints management page.
     */
    public function complaintsManager($stage = null)
    {
        $pageTitle = 'Complaint Management';
        if ($stage) {
            $pageTitle = $stage . ' - Complaint Management';
        }
        
        return view('livewire.layout.crm-app', [
            'componentType' => 'complaints',
            'pageTitle' => $pageTitle,
            'stage' => $stage
        ]);
    }
}

