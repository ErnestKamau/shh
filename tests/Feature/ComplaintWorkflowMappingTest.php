<?php

namespace Tests\Feature;

use App\Models\CRM\Complaint;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ComplaintWorkflowMappingTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('TRUNCATE TABLE complaints CASCADE');

        $companyId = DB::table('companies')->value('id') ?? '019dde3f-07d3-73d0-a0f2-a01ac58346b4';

        $this->user = new User();
        $this->user->name = 'Complaint Workflow Tester';
        $this->user->email = 'complaint-workflow-tester@example.com';
        $this->user->password = bcrypt('password');
        $this->user->company_id = $companyId;
        $this->user->active = 1;
        $this->user->location_id = '0';
        $this->user->is_client = 0;
        $this->user->supplier_id = null;
        $this->user->is_online = 0;
        $this->user->save();
    }

    public function test_visible_complaint_workflow_menu_uses_sparse_status_ids(): void
    {
        $this->seedComplaintsForStage(1, 2);
        $this->seedComplaintsForStage(2, 3);
        $this->seedComplaintsForStage(4, 4);
        $this->seedComplaintsForStage(5, 5);
        $this->seedComplaintsForStage(6, 6);

        $this->assertSame([
            'All Complaints' => 0,
            'Open Complaint' => 1,
            'Complaint Resolution' => 2,
            'Resolution Approval' => 4,
            'Closed Complaint' => 5,
            'Cancelled' => 6,
        ], getComplaintWorkflowMenuItems());

        $this->assertSame(20, getAllComplaints());
        $this->assertSame(2, getComplaintsInWorkflow(1));
        $this->assertSame(3, getComplaintsInWorkflow(2));
        $this->assertSame(4, getComplaintsInWorkflow(4));
        $this->assertSame(5, getComplaintsInWorkflow(5));
        $this->assertSame(6, getComplaintsInWorkflow(6));
    }

    public function test_legacy_approval_uses_sparse_stage_mapping_for_resolution_to_approval(): void
    {
        $complaint = $this->createComplaint(2);

        $response = $this->withoutMiddleware()
            ->actingAs($this->user)
            ->post(route('approve-complaint', ['id' => $complaint->id]), [
                'comment' => 'Approve resolution',
            ]);

        $response->assertRedirect(route('complaint-workflow', ['stage' => 'Complaint Resolution']));
        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'complaint_workflow' => 4,
        ]);
    }

    public function test_closing_a_complaint_changes_closed_count_without_touching_cancelled(): void
    {
        $closingComplaint = $this->createComplaint(4);
        $this->createComplaint(5);
        $this->createComplaint(6, [
            'rejected' => 1,
            'reject_workflow' => 2,
        ]);

        $this->assertSame(1, getComplaintsInWorkflow(4));
        $this->assertSame(1, getComplaintsInWorkflow(5));
        $this->assertSame(1, getComplaintsInWorkflow(6));

        $response = $this->withoutMiddleware()
            ->actingAs($this->user)
            ->post(route('approve-complaint', ['id' => $closingComplaint->id]), [
                'comment' => 'Final approval',
            ]);

        $response->assertRedirect(route('complaint-workflow', ['stage' => 'Resolution Approval']));

        $this->assertSame(0, getComplaintsInWorkflow(4));
        $this->assertSame(2, getComplaintsInWorkflow(5));
        $this->assertSame(1, getComplaintsInWorkflow(6));

        $closingComplaint->refresh();
        $this->assertTrue((bool) $closingComplaint->is_closed);
        $this->assertSame(5, (int) $closingComplaint->complaint_workflow);
    }

    public function test_rejecting_a_complaint_only_changes_cancelled_count(): void
    {
        $complaint = $this->createComplaint(4);
        $this->createComplaint(5);

        $this->assertSame(1, getComplaintsInWorkflow(5));
        $this->assertSame(0, getComplaintsInWorkflow(6));

        $response = $this->withoutMiddleware()
            ->actingAs($this->user)
            ->post(route('reject-complaint', ['id' => $complaint->id]), [
                'comment' => 'Cancelled by reviewer',
            ]);

        $response->assertRedirect(route('complaint-workflow', ['stage' => 'Resolution Approval']));

        $this->assertSame(1, getComplaintsInWorkflow(5));
        $this->assertSame(1, getComplaintsInWorkflow(6));

        $complaint->refresh();
        $this->assertSame(6, (int) $complaint->complaint_workflow);
        $this->assertSame(1, (int) $complaint->rejected);
        $this->assertFalse((bool) $complaint->is_closed);
    }

    protected function seedComplaintsForStage(int $workflowStage, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $this->createComplaint($workflowStage, [
                'complaint_id' => sprintf('CMP-%d-%02d', $workflowStage, $i + 1),
            ]);
        }
    }

    protected function createComplaint(int $workflowStage, array $overrides = []): Complaint
    {
        $defaults = [
            'complaint_id' => sprintf('CMP-%d-%s', $workflowStage, uniqid()),
            'description' => 'Workflow mapping regression test complaint',
            'priority' => 'high',
            'received_from' => 'Test Customer',
            'registered_by' => 'QA User',
            'complaint_workflow' => $workflowStage,
            'is_closed' => $workflowStage === 5,
            'rejected' => $workflowStage === 6,
            'type' => 'Testing',
            'date' => now(),
            'reject_workflow' => $workflowStage === 6 ? 2 : null,
        ];

        return Complaint::create(array_merge($defaults, $overrides));
    }
}
