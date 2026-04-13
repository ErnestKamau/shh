<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerFeedback;
use App\Models\CRM\Complaint;
use App\Models\CRM\Complaint_Type;
use App\Models\CRM\Chain_of_Custody_Complaint;
use App\Models\CRM\Complaintnotes;
use App\Models\CRM\Complaintattachment;
use App\Models\CRM\Complaintsresolutions;
use App\User;

class CRMModuleTest extends DuskTestCase
{
    /**
     * Helper method to clear session and ensure clean state
     */
    private function clearSession(Browser $browser): void
    {
        // Clear any existing session
        $browser->driver->manage()->deleteAllCookies();
        $browser->visit('/_dusk/logout');
        $browser->pause(1000);
    }

    /**
     * Helper method to authenticate user for CRM tests
     */
    private function authenticateUser(Browser $browser): void
    {
        $browser->visit('/login')
            ->pause(1000)
            ->type('input[name="email"]', 'karokin35@gmail.com')
            ->type('input[name="password"]', 'test1234')
            ->press('button[type="submit"]')
            ->pause(2000);
    }

    // ===========================================
    // CUSTOMER REGISTER TESTS
    // ===========================================

    /**
     * Test CRM Customer Register Dashboard
     */
    public function testCRMCustomerRegisterDashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $this->clearSession($browser);
            $this->authenticateUser($browser);
            
            $browser->visit('/crm-home')
                ->pause(3000)
                ->assertSee('Customer List')
                ->assertSee('Add');
        });
    }

    /**
     * Test CRM Customer List View
     */
    public function testCRMCustomerListView(): void
    {
        $this->browse(function (Browser $browser) {
            $this->clearSession($browser);
            $this->authenticateUser($browser);
            
            $browser->visit('/crm-home')
                ->pause(3000)
                ->assertSee('Customer List')
                ->assertVisible('table.table')
                ->assertSee('Code')
                ->assertSee('Name')
                ->assertSee('Email')
                ->assertSee('Phone 1')
                ->assertSee('Active?')
                ->assertVisible('input[type="search"]');
        });
    }

    public function testComplaintWorkflowAllComplaints(): void
    {
        $this->browse(function (Browser $browser) {
            $this->clearSession($browser);
            $this->authenticateUser($browser);
            
            $browser->visit('/complaint/All Complaints')
                ->pause(3000)
                ->assertSee('All Complaints');
        });
    }

    /**
     * Test Complaint Add Modal
     */
    public function testComplaintOpenComplaints(): void
    {
        $this->browse(function (Browser $browser) {
            $this->clearSession($browser);
            $this->authenticateUser($browser);
            
            $browser->visit('/complaint/Open Complaints')
                ->pause(3000)
                ->assertSee('Open Complaints');
        });
    }

    public function testComplaintApproval(): void
    {
        $this->browse(function (Browser $browser) {
            $this->clearSession($browser);
            $this->authenticateUser($browser);
            
            $browser->visit('/complaint/Complaints Approval')
                    ->assertSee('Complaints Approval');
        });
    }

    /**
     * Test Complaint Workflow Stages
     */
    public function testComplaintResolution(): void
    {
        $this->browse(function (Browser $browser) {
            $this->clearSession($browser);
            $this->authenticateUser($browser);

                $browser->visit('/complaint/Complaints Resolution')
                    ->pause(2000)
                    ->assertSee('Complaints Resolution');
        });
    }

    public function testComplaintResolutionApproval(): void
    {
        $this->browse(function (Browser $browser) {
            $this->clearSession($browser);
            $this->authenticateUser($browser);
                $browser->visit('complaint/Resolution Approval')
                    ->pause(2000)
                    ->assertSee('Resolution Approval');
        });
    }

    public function testClosedComplaints(): void
    {
        $this->browse(function (Browser $browser) {
            $this->clearSession($browser);
            $this->authenticateUser($browser);
                $browser->visit('/complaint/Closed Complaints')
                    ->pause(3000)
                    ->assertSee('Closed Complaints');
        });
    }

    public function testCancelledComplaints(): void
    {
        $this->browse(function (Browser $browser) {
            $this->clearSession($browser);
            $this->authenticateUser($browser);
                $browser->visit('/complaint/Cancelled Complaints')
                    ->pause(3000)
                    ->assertSee('Cancelled Complaints');
        });
    }

    public function testComplaintTypeDashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $this->clearSession($browser);
            $this->authenticateUser($browser);
            
            $browser->visit('/complaint-type/home')
                ->pause(3000)
                ->assertSee('Complaint Types');
        });
    }

    public function testCRMBatchReportsDashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $this->clearSession($browser);
            $this->authenticateUser($browser);
            
            $browser->visit('/crm-batch-reports')
                ->pause(3000)
                ->assertSee('Batch Report');
        });
    }


    public function testCustomerFeedbackDashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $this->clearSession($browser);
            $this->authenticateUser($browser);
            
            $browser->visit('/customer-feedback/home')
                ->pause(3000)
                ->assertSee('Customer Feedback');
        });
    }

}
