<?php

namespace App\Services\Portal;

use App\DTOs\Portal\Crm\CreatedComplaintDTO;
use App\Models\CRM\Chain_of_Custody_Complaint;
use App\Models\CRM\Complaint;
use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use App\Repositories\PortalCrmRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PortalComplaintService
{
    public function __construct(
        private readonly PortalCrmRepository $repository,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(string $customerId, array $data, ?string $portalAccountId = null): CreatedComplaintDTO
    {
        return DB::transaction(function () use ($customerId, $data, $portalAccountId): CreatedComplaintDTO {
            $complaint = new Complaint();
            $complaint->complaint_id = $this->generateComplaintNumber();
            $complaint->description = (string) $data['description'];
            $complaint->priority = (string) $data['priority'];
            $complaint->type = (string) $data['type'];
            $complaint->organization_name = (string) $data['organization_name'];
            $complaint->contact_name = (string) $data['contact_name'];
            $complaint->date = $data['date'] ?? now();
            $complaint->received_from_type = (string) ($data['received_from_type'] ?? 'Customer');
            $complaint->is_lab_related = (bool) ($data['is_lab_related'] ?? false);
            $complaint->nature_of_complaint = $data['nature_of_complaint'] ?? null;
            $complaint->test_item = $data['test_item'] ?? null;
            $complaint->report_serial_no = $data['report_serial_no'] ?? null;
            $complaint->title_position = $data['title_position'] ?? null;
            $complaint->mode_of_delivery = $data['mode_of_delivery'] ?? 'Portal';
            $complaint->received_from = (string) ($data['submitter_name'] ?? $data['organization_name']);
            $complaint->registered_by = (string) ($data['submitter_name'] ?? 'Portal Customer');
            $complaint->complaint_workflow = 1;
            $complaint->client_id = $customerId;
            $complaint->is_closed = false;
            $complaint->save();

            $chain = new Chain_of_Custody_Complaint();
            $chain->complaint_id = $complaint->id;
            $chain->action = 'Create complaint';
            $chain->action_taker_id = 0;
            $chain->comments = $portalAccountId !== null
                ? 'Submitted via customer portal (account: '.$portalAccountId.')'
                : 'Submitted via customer portal';
            $chain->workflow_stage = getComplaintWorkflow()[$complaint->complaint_workflow] ?? (string) $complaint->complaint_workflow;
            $chain->save();

            $this->notifyPersonnel($complaint);

            Log::channel('daily')->info('portal.complaint.created', [
                'complaint_id' => $complaint->id,
                'customer_id' => $customerId,
                'portal_account_id' => $portalAccountId,
            ]);

            return new CreatedComplaintDTO(
                id: (string) $complaint->id,
                complaintNumber: (string) $complaint->complaint_id,
                status: $this->publicStatusLabel((string) $complaint->complaint_workflow),
                resolutionStatus: 'open',
                createdAt: $complaint->date?->toIso8601String(),
            );
        });
    }

    public function list(string $customerId, int $page, int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginateComplaints($customerId, $perPage);
    }

    /**
     * @return list<\App\DTOs\Portal\Crm\ComplaintTypeDTO>
     */
    public function types(): array
    {
        return $this->repository->activeComplaintTypes();
    }

    private function generateComplaintNumber(): string
    {
        $total = Complaint::query()->count() + 1;

        return 'COMP/'.$total;
    }

    private function notifyPersonnel(Complaint $complaint): void
    {
        $config = SystemConfigurationsType::query()
            ->where('configuration_type', 'Personnel to Recieve Feedback and Complaint Notification')
            ->first();

        if (! $config) {
            return;
        }

        $company = getCompanyDetails();
        $users = SystemConfiguration::query()
            ->where('configuration_type_id', $config->id)
            ->get();

        foreach ($users as $user) {
            $subject = '['.$company['name'].'] Complaint Notification - '.$complaint->complaint_id;
            $body = 'Hi '.$user->key.', <br> A new complaint <b>'.$complaint->complaint_id.'</b> was submitted via the customer portal.<br>Regards '.$company['name'];
            notify_user($body, $user->value, $subject);
        }
    }

    private function publicStatusLabel(string $status): string
    {
        if (is_numeric($status)) {
            $workflow = getComplaintWorkflow();

            return $workflow[(int) $status] ?? 'Open';
        }

        return str($status)->replace('_', ' ')->title()->toString();
    }
}
