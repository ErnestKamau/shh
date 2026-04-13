<?php

namespace Tests\Feature;

use App\Models\CRM\CRMCustomer;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use App\Livewire\Crm\Customer\Tabs\CustomerUnitsTab;
use Tests\TestCase;

/**
 * Tests for CRM customer show page and tab components.
 *
 * Requires a configured test database (see phpunit.xml DB_DATABASE).
 */
class CrmCustomerTabsTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected CRMCustomer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'company_id' => 1,
        ]);

        $this->customer = CRMCustomer::create([
            'name' => 'Test Customer Tabs',
            'code' => 'TCT001',
            'company_id' => 1,
            'active' => 1,
            'email' => 'test@example.com',
            'telephone1' => '1234567890',
            'telephone2' => '',
            'website' => 'https://example.com',
            'country_id' => 1,
            'postal_address' => null,
            'physical_address' => null,
            'fax' => null,
        ]);
    }

    public function test_customer_show_page_loads_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('show-customer', $this->customer->id));

        $response->assertStatus(200);
        $response->assertSee('Test Customer Tabs');
        $response->assertSeeLivewire(\App\Livewire\Crm\Customer\Show::class);
    }

    public function test_company_units_manager_component_mounts(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CustomerUnitsTab::class, ['customer' => $this->customer])
            ->assertSee('Company Units');
    }

    public function test_company_units_manager_can_create_unit(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CustomerUnitsTab::class, ['customer' => $this->customer])
            ->call('openUnitForm')
            ->set('name', 'New Unit')
            ->set('active', true)
            ->call('save');

        $this->assertDatabaseHas('crm_company_units', [
            'crm_customer_id' => $this->customer->id,
            'name' => 'New Unit',
        ]);
    }
}
