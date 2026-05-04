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
            'pageTitle' => __('crm.customer_management')
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
            'pageTitle' => __('crm.customer_profile') . ' - ' . $customer->name,
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
            'pageTitle' => __('crm.sample_points_management')
        ]);
    }

    /**
     * Display the areas management page.
     */
    public function areas()
    {
        return view('livewire.layout.crm-app', [
            'componentType' => 'areas',
            'pageTitle' => __('crm.areas_management')
        ]);
    }
    /**
     * Display the complaints management page.
     */
    public function complaintsManager($stage = null)
    {
        $pageTitle = __('crm.complaint_management');
        if ($stage) {
            $pageTitle = $stage . ' - ' . __('crm.complaint_management');
        }
        
        return view('livewire.layout.crm-app', [
            'componentType' => 'complaints',
            'pageTitle' => $pageTitle,
            'stage' => $stage
        ]);
    }

    /**
     * Display the customer feedback management page.
     */
    public function feedbacks()
    {
        return view('livewire.layout.crm-app', [
            'componentType' => 'feedbacks',
            'pageTitle' => __('crm.customer_feedback')
        ]);
    }

    /**
     * Display the CRM dashboard page.
     */
    public function dashboard()
    {
        return view('livewire.layout.crm-app', [
            'componentType' => 'dashboard',
            'pageTitle' => __('crm.crm_management')
        ]);
    }
}

