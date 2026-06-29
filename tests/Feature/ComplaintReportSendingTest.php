<?php

namespace Tests\Feature;

use App\Livewire\Crm\Complaint\ComplaintShow;
use App\Livewire\Crm\Complaint\Tabs\ComplaintInvestigationTab;
use App\Livewire\Crm\Complaint\Tabs\ComplaintWorkflowTab;
use App\Mail\ComplaintClosureMail;
use App\Models\CRM\CapaRecord;
use App\Models\CRM\Complaint;
use App\Models\CRM\Complaintattachment;
use App\Models\CRM\Complaintsresolutions;
use App\Models\CRM\CRMCustomer;
use App\Services\CRM\ComplaintInvestigationReportService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ComplaintReportSendingTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected CRMCustomer $customer;
    protected string $contactId;
    protected string $contactEmail = 'contact@example.com';

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('TRUNCATE TABLE complaints CASCADE');
        DB::table('companies')->where('id', '00000000-0000-0000-0000-000000000001')->delete();

        $countryId = DB::table('countries')->value('id') ?? '2226323f-012d-4979-8c0e-f45a2a5b54fa';
        $companyId = '00000000-0000-0000-0000-000000000001';

        DB::table('companies')->insert([
            'id' => $companyId,
            'name' => 'Test Lab',
            'logo' => '/images/no-logo.png',
            'location' => 'Nairobi',
            'address' => 'Test Address',
            'country_id' => $countryId,
            'website' => 'https://example.com',
            'active' => 1,
            'show_on_reports' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->user = new User();
        $this->user->name = 'Complaint Mail Tester';
        $this->user->email = 'complaint-mail-tester@example.com';
        $this->user->password = bcrypt('password');
        $this->user->company_id = $companyId;
        $this->user->active = 1;
        $this->user->location_id = '0';
        $this->user->is_client = 0;
        $this->user->supplier_id = null;
        $this->user->is_online = 0;
        $this->user->save();

        $this->customer = CRMCustomer::create([
            'name' => 'Complaint Mail Customer',
            'code' => 'CMC001',
            'company_id' => $companyId,
            'active' => 1,
            'email' => 'customer@example.com',
            'telephone1' => '123456789',
            'telephone2' => '',
            'website' => 'https://example.com',
            'country_id' => $countryId,
            'postal_address' => 'P.O. Box 1',
            'physical_address' => 'Nairobi',
            'fax' => null,
        ]);

        $contactId = (string) \Illuminate\Support\Str::uuid();
        DB::table('crm_customer_contacts')->insert([
            'id' => $contactId,
            'first_name' => 'Dennis',
            'middle_name' => '',
            'last_name' => 'Kinyua',
            'job_occupation' => 'QA',
            'unit_name' => 'Support',
            'email' => $this->contactEmail,
            'telephone' => '123456789',
            'mobile' => '123456789',
            'receive_price_list' => 0,
            'receive_invoice' => 0,
            'receive_report' => 1,
            'company_id' => $companyId,
            'crm_customer_id' => $this->customer->id,
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'can_login' => 0,
        ]);
        $this->contactId = $contactId;

        $this->actingAs($this->user);
        session([
            'permissions' => [
                'CRM' => [
                    'components' => [
                        'Complaint Pending Closure' => ['Edit' => 'true'],
                        'Complaint Investigation' => ['Edit' => 'true'],
                        'Resolution Approval' => ['Edit' => 'true'],
                        'Complaints' => ['View' => 'true'],
                    ],
                ],
            ],
        ]);
    }

    public function test_approve_next_without_send_advances_without_mailing(): void
    {
        Mail::fake();

        $complaint = $this->createComplaint(1);

        Livewire::test(ComplaintWorkflowTab::class, ['complaintId' => $complaint->id])
            ->set('modalAction', 'approveNext')
            ->call('performAction')
            ->assertHasNoErrors();

        $complaint->refresh();

        $this->assertSame(2, (int) $complaint->complaint_workflow);
        Mail::assertNothingSent();
    }

    public function test_approve_next_with_send_to_selected_contacts_mails_then_advances_and_refreshes_attachment(): void
    {
        Mail::fake();
        Storage::fake('public');

        $complaint = $this->createComplaint(2);
        $this->createResolution($complaint);

        Livewire::test(ComplaintWorkflowTab::class, ['complaintId' => $complaint->id])
            ->set('modalAction', 'approveNext')
            ->set('send_to_customer', true)
            ->set('selectedContactIds', [$this->contactId])
            ->call('performAction')
            ->assertHasNoErrors();

        $complaint->refresh();

        $this->assertSame(4, (int) $complaint->complaint_workflow);

        Mail::assertSent(ComplaintClosureMail::class, function (ComplaintClosureMail $mail) {
            return $mail->hasTo($this->contactEmail);
        });

        $attachment = $this->assertSingleActiveAttachmentType($complaint, 'Investigation Report');
        Storage::disk('public')->assertExists($this->storagePathFromAttachment($attachment));

        $this->assertDatabaseHas('chain_of_custody_complaints', [
            'complaint_id' => $complaint->id,
            'action' => 'Investigation report emailed during Resolution approval.',
        ]);
    }

    public function test_approve_next_requires_selected_contacts_when_send_is_enabled(): void
    {
        Mail::fake();
        Storage::fake('public');

        $complaint = $this->createComplaint(2);
        $this->createResolution($complaint);

        Livewire::test(ComplaintWorkflowTab::class, ['complaintId' => $complaint->id])
            ->set('modalAction', 'approveNext')
            ->set('send_to_customer', true)
            ->set('selectedContactIds', [])
            ->call('performAction')
            ->assertHasErrors(['selectedContactIds']);

        $complaint->refresh();

        $this->assertSame(2, (int) $complaint->complaint_workflow);
        Mail::assertNothingSent();
        $this->assertSame(0, $this->activeAttachmentCount($complaint, 'Investigation Report'));
    }

    public function test_approve_next_does_not_advance_when_report_send_fails(): void
    {
        Mail::fake();
        Storage::fake('public');

        $complaint = $this->createComplaint(2);
        $this->createResolution($complaint);

        $this->mock(ComplaintInvestigationReportService::class, function ($mock) {
            $mock->shouldReceive('sendToSelectedContacts')
                ->once()
                ->andThrow(new \RuntimeException('Unable to send the investigation report right now. Please try again.'));
        });

        Livewire::test(ComplaintWorkflowTab::class, ['complaintId' => $complaint->id])
            ->set('modalAction', 'approveNext')
            ->set('send_to_customer', true)
            ->set('selectedContactIds', [$this->contactId])
            ->call('performAction')
            ->assertSet('workflowState', 'error');

        $complaint->refresh();

        $this->assertSame(2, (int) $complaint->complaint_workflow);
        Mail::assertNothingSent();
        $this->assertSame(0, $this->activeAttachmentCount($complaint, 'Investigation Report'));
    }

    public function test_approve_and_notify_uses_selected_contact_ids_for_sending(): void
    {
        Mail::fake();
        Storage::fake('public');

        $complaint = $this->createComplaint(4);
        $this->createResolution($complaint);

        Livewire::test(ComplaintWorkflowTab::class, ['complaintId' => $complaint->id])
            ->set('modalAction', 'approveAndNotify')
            ->set('send_to_customer', true)
            ->set('selectedContactIds', [$this->contactId])
            ->set('internal_remarks', 'Reviewed and approved')
            ->set('client_remarks', 'Please see attached report')
            ->call('performAction')
            ->assertHasNoErrors();

        Mail::assertSent(ComplaintClosureMail::class, function (ComplaintClosureMail $mail) {
            return $mail->hasTo($this->contactEmail);
        });

        $this->assertSingleActiveAttachmentType($complaint, 'Investigation Report');

        $this->assertDatabaseHas('chain_of_custody_complaints', [
            'complaint_id' => $complaint->id,
            'action' => 'Resolution Approved by ' . $this->user->name . '. Report sent to client: Yes',
        ]);
    }

    public function test_manual_email_report_uses_shared_sender_for_client_email_and_attachment(): void
    {
        Mail::fake();
        Storage::fake('public');

        $complaint = $this->createComplaint(2);
        $this->createResolution($complaint);

        Livewire::test(ComplaintShow::class, ['id' => $complaint->id])
            ->call('emailReport', $complaint->id);

        Mail::assertSent(ComplaintClosureMail::class, function (ComplaintClosureMail $mail) {
            return $mail->hasTo('customer@example.com');
        });

        $this->assertSingleActiveAttachmentType($complaint, 'Investigation Report');

        $this->assertDatabaseHas('chain_of_custody_complaints', [
            'complaint_id' => $complaint->id,
            'action' => 'Investigation report emailed manually to customer.',
        ]);
    }

    public function test_save_investigation_creates_or_replaces_generated_investigation_attachment(): void
    {
        Storage::fake('public');

        $complaint = $this->createComplaint(2);
        $this->createResolution($complaint, ['car_required' => false, 'ncr_required' => false]);

        Storage::disk('public')->put('complaints/investigations/old-report.pdf', 'old report');

        Complaintattachment::create([
            'complaint_id' => $complaint->id,
            'title' => 'Laboratory Investigation Report',
            'type' => 'Investigation Report',
            'description' => 'Old generated report',
            'file_path' => '/storage/complaints/investigations/old-report.pdf',
            'posted_by' => $this->user->name,
            'is_public' => true,
            'is_delete' => 0,
        ]);

        Livewire::test(ComplaintInvestigationTab::class, ['complaintId' => $complaint->id])
            ->set('cause_of_complaint', 'Investigation found the root issue.')
            ->set('car_required', false)
            ->set('ncr_required', false)
            ->call('saveInvestigation');

        $attachment = $this->assertSingleActiveAttachmentType($complaint, 'Investigation Report');
        Storage::disk('public')->assertExists($this->storagePathFromAttachment($attachment));
        Storage::disk('public')->assertMissing('complaints/investigations/old-report.pdf');
    }

    public function test_close_without_car_or_ncr_generates_closure_report_only(): void
    {
        Storage::fake('public');

        $complaint = $this->createComplaint(4);
        $this->createResolution($complaint, [
            'car_required' => false,
            'ncr_required' => false,
        ]);

        Livewire::test(ComplaintWorkflowTab::class, ['complaintId' => $complaint->id])
            ->set('client_remarks', 'Customer accepted the closure.')
            ->set('complaint_review_remarks', 'Final closure approved internally.')
            ->call('saveAndCloseComplaint');

        $complaint->refresh();

        $this->assertSame(5, (int) $complaint->complaint_workflow);
        $this->assertSame(['Closure Report'], $this->activeAttachmentTypes($complaint));
    }

    public function test_close_with_capa_generates_closure_and_capa_reports(): void
    {
        Storage::fake('public');

        $complaint = $this->createComplaint(4);
        $this->createResolution($complaint, [
            'car_required' => true,
            'ncr_required' => false,
        ]);
        $this->createCapaRecord($complaint);

        Livewire::test(ComplaintWorkflowTab::class, ['complaintId' => $complaint->id])
            ->set('client_remarks', 'Customer accepted the closure.')
            ->set('complaint_review_remarks', 'CAPA path completed successfully.')
            ->call('saveAndCloseComplaint');

        $complaint->refresh();

        $this->assertSame(5, (int) $complaint->complaint_workflow);
        $this->assertSame(['CAPA Report', 'Closure Report'], $this->activeAttachmentTypes($complaint));
    }

    public function test_close_with_capa_and_ncr_generates_all_reports(): void
    {
        Storage::fake('public');

        $complaint = $this->createComplaint(4);
        $this->createResolution($complaint, [
            'car_required' => true,
            'ncr_required' => true,
        ]);
        $this->createCapaRecord($complaint);

        Livewire::test(ComplaintWorkflowTab::class, ['complaintId' => $complaint->id])
            ->set('client_remarks', 'Customer accepted the closure.')
            ->set('complaint_review_remarks', 'CAPA and NCR path completed successfully.')
            ->call('saveAndCloseComplaint');

        $complaint->refresh();

        $this->assertSame(5, (int) $complaint->complaint_workflow);
        $this->assertSame(['CAPA Report', 'Closure Report', 'NCR Report'], $this->activeAttachmentTypes($complaint));
    }

    public function test_close_generation_failure_blocks_the_transition(): void
    {
        Storage::fake('public');

        $complaint = $this->createComplaint(4);
        $this->createResolution($complaint, [
            'car_required' => false,
            'ncr_required' => false,
        ]);

        $this->mock(ComplaintInvestigationReportService::class, function ($mock) {
            $mock->shouldReceive('generateAndAttachCloseReports')
                ->once()
                ->andThrow(new \RuntimeException('Unable to save the generated report attachment.'));
        });

        Livewire::test(ComplaintWorkflowTab::class, ['complaintId' => $complaint->id])
            ->set('client_remarks', 'Customer accepted the closure.')
            ->set('complaint_review_remarks', 'Final closure approved internally.')
            ->call('saveAndCloseComplaint')
            ->assertDispatched('alert');

        $complaint->refresh();

        $this->assertSame(4, (int) $complaint->complaint_workflow);
        $this->assertFalse((bool) $complaint->is_closed);
        $this->assertSame([], $this->activeAttachmentTypes($complaint));
    }

    protected function createComplaint(int $workflowStage): Complaint
    {
        return Complaint::create([
            'complaint_id' => sprintf('CMP-MAIL-%s', uniqid()),
            'description' => 'Complaint mail regression test',
            'priority' => 'high',
            'received_from' => 'Complaint Mail Customer',
            'registered_by' => 'QA User',
            'complaint_workflow' => $workflowStage,
            'is_closed' => false,
            'rejected' => false,
            'type' => 'Testing',
            'date' => now(),
            'client_id' => $this->customer->id,
            'organization_name' => $this->customer->name,
            'contact_name' => 'Dennis Kinyua',
            'title_position' => 'QA',
            'mode_of_delivery' => 'Email',
            'received_from_type' => 'Customer',
            'is_lab_related' => true,
            'test_item' => 'Water Sample',
            'report_serial_no' => 'RPT-001',
        ]);
    }

    protected function createResolution(Complaint $complaint, array $overrides = []): Complaintsresolutions
    {
        return Complaintsresolutions::create(array_merge([
            'complaint_id' => $complaint->id,
            'registered_by' => $this->user->name,
            'car_no' => 'CAR-001',
            'findings' => 'Findings captured during investigation.',
            'action_taken' => 'Immediate action was taken.',
            'corrective_action_taken' => 'Corrective action was implemented.',
            'root_cause_analysis' => 'Root cause identified.',
            'preventive_action' => 'Preventive action recorded.',
            'officer_responsible' => 'QA Officer',
            'workflow_stage' => $complaint->complaint_workflow,
            'client_remarks' => 'Client remarks.',
            'internal_remarks' => 'Internal closure remarks.',
            'car_required' => false,
            'ncr_required' => false,
            'date_issued' => now(),
            'proposed_close_out_date' => now()->addDay(),
            'issued_to' => 'QA Team',
            'issued_by' => 'Lab Manager',
            'car_type' => 'Major',
            'risk_level' => 'High',
            'capa_identified_by' => 'Lab Manager',
            'capa_identified_date' => now(),
        ], $overrides));
    }

    protected function createCapaRecord(Complaint $complaint, array $overrides = []): CapaRecord
    {
        return CapaRecord::create(array_merge([
            'complaint_id' => $complaint->id,
            'details_of_non_conformance' => 'Detailed non-conformance description.',
            'root_cause' => 'Documented root cause.',
            'effectiveness_verified_by' => 'Verifier User',
            'effectiveness_date' => now(),
            'why_why_analysis' => [
                'problem_statement' => 'Problem statement',
                'why_1' => 'First why',
                'why_2' => 'Second why',
                'root_cause_analysis' => 'Root cause',
                'corrective_action_taken' => 'Corrective action',
            ],
            'lab_no' => 'LAB-001',
            'ncr_identified_date' => now(),
        ], $overrides));
    }

    protected function activeAttachmentCount(Complaint $complaint, string $type): int
    {
        return Complaintattachment::where('complaint_id', $complaint->id)
            ->where('type', $type)
            ->where('is_delete', '!=', 1)
            ->count();
    }

    /**
     * @return array<int, string>
     */
    protected function activeAttachmentTypes(Complaint $complaint): array
    {
        return Complaintattachment::where('complaint_id', $complaint->id)
            ->where('is_delete', '!=', 1)
            ->orderBy('type')
            ->pluck('type')
            ->values()
            ->all();
    }

    protected function assertSingleActiveAttachmentType(Complaint $complaint, string $type): Complaintattachment
    {
        $this->assertSame(1, $this->activeAttachmentCount($complaint, $type));

        return Complaintattachment::where('complaint_id', $complaint->id)
            ->where('type', $type)
            ->where('is_delete', '!=', 1)
            ->latest('id')
            ->firstOrFail();
    }

    protected function storagePathFromAttachment(Complaintattachment $attachment): string
    {
        return ltrim((string) preg_replace('#^/storage/#', '', $attachment->file_path), '/');
    }
}
