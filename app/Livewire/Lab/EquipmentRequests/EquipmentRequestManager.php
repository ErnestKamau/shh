<?php

namespace App\Livewire\Lab\EquipmentRequests;

use App\Directorate;
use App\Lab;
use App\Models\Equipments\Equipment;
use App\Models\Lab\EquipmentUsageRequest;
use App\Models\Lab\LabUserNotification;
use App\Services\Lab\EquipmentUsageRequestService;
use App\Services\Lab\SampleZoneQueryService;
use App\Services\Lab\UserZoneResolver;
use App\User;
use App\Zone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class EquipmentRequestManager extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $activeTab = 'my_requests';

    public string $search = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public int $perPage = 15;

    /** @var array<int, int> */
    public array $perPageOptions = [10, 15, 25, 50];

    public bool $showAdvancedFilters = false;

    public string $filterZoneId = '';

    public string $filterDirectorateId = '';

    public string $filterLabId = '';

    public string $message = '';

    public string $messageType = 'success';

    public bool $showCreateModal = false;

    public bool $showDetailModal = false;

    public bool $showApprovalModal = false;

    public bool $showNotificationsPanel = false;

    public ?string $selectedRequestId = null;

    public ?EquipmentUsageRequest $selectedRequest = null;

    // Create form
    public string $equipmentSearch = '';

    public ?string $equipment_id = null;

    public string $selectedEquipmentName = '';

    public string $request_comment = '';

    public string $proposed_start_at = '';

    public string $proposed_end_at = '';

    /** @var array<int, string> */
    public array $sample_detail_ids = [];

    public string $sampleSearch = '';

    // Approval form
    public string $approval_decision = 'approve';

    public string $approval_comment = '';

    public string $approved_start_at = '';

    public string $approved_end_at = '';

    public ?string $helping_analyst_id = null;

    public string $analystSearch = '';

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'refreshEquipmentRequests' => '$refresh',
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', EquipmentUsageRequest::class);

        if ($this->canApprove() && ! $this->canAdd()) {
            $this->activeTab = 'submitted';
        }
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage('requestsPage');
    }

    public function updatedSearch(): void
    {
        $this->resetPage('requestsPage');
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage('requestsPage');
    }

    public function updatedDateTo(): void
    {
        $this->resetPage('requestsPage');
    }

    public function updatedPerPage(): void
    {
        $this->resetPage('requestsPage');
    }

    public function updatedFilterZoneId(): void
    {
        $this->resetPage('requestsPage');
    }

    public function updatedFilterDirectorateId(): void
    {
        $this->filterLabId = '';
        $this->resetPage('requestsPage');
    }

    public function updatedFilterLabId(): void
    {
        $this->resetPage('requestsPage');
    }

    public function toggleAdvancedFilters(): void
    {
        $this->showAdvancedFilters = ! $this->showAdvancedFilters;
    }

    public function canAdd(): bool
    {
        return Auth::user()?->can('laboratory.components.equipment-requests.add') ?? false;
    }

    public function canApprove(): bool
    {
        return Auth::user()?->can('laboratory.components.equipment-requests.approve') ?? false;
    }

    public function openCreateModal(): void
    {
        $this->authorize('create', EquipmentUsageRequest::class);
        $this->resetCreateForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetCreateForm();
    }

    public function resetCreateForm(): void
    {
        $this->equipmentSearch = '';
        $this->equipment_id = null;
        $this->selectedEquipmentName = '';
        $this->request_comment = '';
        $this->proposed_start_at = '';
        $this->proposed_end_at = '';
        $this->sample_detail_ids = [];
        $this->sampleSearch = '';
        $this->resetValidation();
    }

    public function selectEquipment(string $id): void
    {
        $equipment = $this->availableEquipmentQuery()->find($id);

        if (! $equipment) {
            return;
        }

        $this->equipment_id = $equipment->id;
        $this->selectedEquipmentName = $equipment->name.' ('.$equipment->equipment_number.')';
        $this->equipmentSearch = $this->selectedEquipmentName;
    }

    public function clearEquipment(): void
    {
        $this->equipment_id = null;
        $this->selectedEquipmentName = '';
        $this->equipmentSearch = '';
    }

    public function toggleSample(string $sampleDetailId): void
    {
        if (in_array($sampleDetailId, $this->sample_detail_ids, true)) {
            $this->sample_detail_ids = array_values(array_diff($this->sample_detail_ids, [$sampleDetailId]));
        } else {
            $this->sample_detail_ids[] = $sampleDetailId;
        }
    }

    public function submitRequest(EquipmentUsageRequestService $service): void
    {
        $this->authorize('create', EquipmentUsageRequest::class);

        $this->validate([
            'equipment_id' => 'required|uuid',
            'proposed_start_at' => 'required|date',
            'proposed_end_at' => 'required|date|after:proposed_start_at',
            'sample_detail_ids' => 'required|array|min:1',
            'request_comment' => 'nullable|string|max:2000',
        ]);

        try {
            $service->submit(Auth::user(), [
                'equipment_id' => $this->equipment_id,
                'request_comment' => $this->request_comment,
                'proposed_start_at' => $this->proposed_start_at,
                'proposed_end_at' => $this->proposed_end_at,
                'sample_detail_ids' => $this->sample_detail_ids,
            ]);

            $this->flash('success', 'Equipment usage request submitted successfully.');
            $this->closeCreateModal();
            $this->activeTab = 'my_requests';
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->flash('danger', 'Could not submit request: '.$e->getMessage());
        }
    }

    public function viewRequest(string $id): void
    {
        $request = EquipmentUsageRequest::with([
            'equipment',
            'sampleDetails',
            'requester',
            'helpingAnalyst',
            'approver',
            'rejector',
        ])->findOrFail($id);

        $this->authorize('view', $request);

        $this->selectedRequestId = $id;
        $this->selectedRequest = $request;
        $this->showDetailModal = true;

        $this->markNotificationsReadForRequest($id);
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedRequestId = null;
        $this->selectedRequest = null;
    }

    public function openApprovalModal(string $id): void
    {
        $request = EquipmentUsageRequest::with('equipment')->findOrFail($id);
        $this->authorize('approve', $request);

        $this->selectedRequestId = $id;
        $this->selectedRequest = $request;
        $this->approval_decision = 'approve';
        $this->approval_comment = '';
        $this->approved_start_at = $request->proposed_start_at?->format('Y-m-d\TH:i') ?? '';
        $this->approved_end_at = $request->proposed_end_at?->format('Y-m-d\TH:i') ?? '';
        $this->helping_analyst_id = null;
        $this->analystSearch = '';
        $this->showApprovalModal = true;
    }

    public function closeApprovalModal(): void
    {
        $this->showApprovalModal = false;
        $this->selectedRequestId = null;
        $this->selectedRequest = null;
    }

    public function selectHelpingAnalyst(string $userId): void
    {
        $this->helping_analyst_id = $userId;
        $user = User::find($userId);
        $this->analystSearch = $user?->name ?? '';
    }

    public function clearHelpingAnalyst(): void
    {
        $this->helping_analyst_id = null;
        $this->analystSearch = '';
    }

    public function submitApproval(EquipmentUsageRequestService $service): void
    {
        $request = EquipmentUsageRequest::findOrFail($this->selectedRequestId);
        $this->authorize('approve', $request);

        if ($this->approval_decision === 'reject') {
            $this->validate([
                'approval_comment' => 'required|string|max:2000',
            ]);

            try {
                $service->reject(Auth::user(), $request, [
                    'approval_comment' => $this->approval_comment,
                ]);
                $this->flash('success', 'Request rejected.');
                $this->closeApprovalModal();
            } catch (\Illuminate\Validation\ValidationException $e) {
                throw $e;
            } catch (\Throwable $e) {
                $this->flash('danger', $e->getMessage());
            }

            return;
        }

        $this->validate([
            'approved_start_at' => 'required|date',
            'approved_end_at' => 'required|date|after:approved_start_at',
            'approval_comment' => 'nullable|string|max:2000',
            'helping_analyst_id' => 'nullable|uuid|exists:users,id',
        ]);

        try {
            $service->approve(Auth::user(), $request, [
                'approved_start_at' => $this->approved_start_at,
                'approved_end_at' => $this->approved_end_at,
                'approval_comment' => $this->approval_comment,
                'helping_analyst_id' => $this->helping_analyst_id,
            ]);
            $this->flash('success', 'Request approved.');
            $this->closeApprovalModal();
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->flash('danger', $e->getMessage());
        }
    }

    public function cancelRequest(string $id, EquipmentUsageRequestService $service): void
    {
        $request = EquipmentUsageRequest::findOrFail($id);
        $this->authorize('cancel', $request);

        try {
            $service->cancel(Auth::user(), $request);
            $this->flash('success', 'Request cancelled.');
            $this->closeDetailModal();
        } catch (\Throwable $e) {
            $this->flash('danger', $e->getMessage());
        }
    }

    public function toggleNotificationsPanel(): void
    {
        $this->showNotificationsPanel = ! $this->showNotificationsPanel;
    }

    public function markNotificationRead(string $notificationId): void
    {
        $notification = LabUserNotification::query()
            ->where('user_id', Auth::id())
            ->findOrFail($notificationId);

        $notification->markAsRead();

        if ($notification->notifiable_id) {
            $this->viewRequest($notification->notifiable_id);
        }
    }

    public function markAllNotificationsRead(): void
    {
        LabUserNotification::query()
            ->where('user_id', Auth::id())
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    protected function markNotificationsReadForRequest(string $requestId): void
    {
        LabUserNotification::query()
            ->where('user_id', Auth::id())
            ->where('notifiable_type', EquipmentUsageRequest::class)
            ->where('notifiable_id', $requestId)
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    protected function flash(string $type, string $text): void
    {
        $this->messageType = $type;
        $this->message = $text;
    }

    protected function availableEquipmentQuery()
    {
        $zoneIds = app(UserZoneResolver::class)->zoneIdsForUser(Auth::user());

        return Equipment::query()
            ->where('active', true)
            ->where(function ($q): void {
                $q->where('is_disposal', false)->orWhereNull('is_disposal');
            })
            ->inZones($zoneIds)
            ->orderBy('name');
    }

    public function getFilteredEquipmentProperty()
    {
        $search = trim($this->equipmentSearch);

        $query = $this->availableEquipmentQuery();

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('equipment_number', 'like', '%'.$search.'%');
            });
        }

        return $query->limit(15)->get();
    }

    public function getFilteredSamplesProperty()
    {
        $zoneIds = app(UserZoneResolver::class)->zoneIdsForUser(Auth::user());
        $search = trim($this->sampleSearch);

        $query = app(SampleZoneQueryService::class)
            ->sampleDetailsInZonesQuery($zoneIds)
            ->addSelect('sample_headers.batch_code')
            ->orderBy('sample_details.sample_code');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('sample_details.sample_code', 'like', '%'.$search.'%')
                    ->orWhere('sample_headers.batch_code', 'like', '%'.$search.'%');
            });
        }

        return $query->limit(20)->get();
    }

    public function getFilteredAnalystsProperty()
    {
        if (! $this->selectedRequest?->zone_id) {
            return collect();
        }

        $zoneId = $this->selectedRequest->zone_id;
        $search = trim($this->analystSearch);

        $query = User::query()
            ->where('active', 1)
            ->where(function ($q) use ($zoneId): void {
                $q->where('zone_id', $zoneId)
                    ->orWhereHas('assignedZones', fn ($z) => $z->where('zones.id', $zoneId))
                    ->orWhereHas('assignedLabs', fn ($l) => $l->where('labs.zone_id', $zoneId));
            });

        if ($search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        return $query->orderBy('name')->limit(15)->get();
    }

    /**
     * @return array{
     *     submitted: int,
     *     approved: int,
     *     scheduled_today: int,
     *     rejected: int,
     *     unread_notifications: int
     * }
     */
    public function getRequestStatsProperty(): array
    {
        $counts = $this->tabCounts;

        return [
            'submitted' => $counts['submitted'],
            'approved' => $counts['approved'],
            'scheduled_today' => $counts['scheduled_today'],
            'rejected' => $counts['rejected'],
            'unread_notifications' => $this->unreadNotificationCount,
        ];
    }

    /**
     * @return array{submitted: int, approved: int, scheduled_today: int, my_requests: int, rejected: int}
     */
    public function getTabCountsProperty(): array
    {
        return [
            'submitted' => $this->countForTab('submitted'),
            'approved' => $this->countForTab('approved'),
            'scheduled_today' => $this->countForTab('scheduled_today'),
            'my_requests' => $this->countForTab('my_requests'),
            'rejected' => $this->countForTab('rejected'),
        ];
    }

    public function getFilterZonesProperty()
    {
        $zoneIds = app(UserZoneResolver::class)->zoneIdsForUser(Auth::user());

        return Zone::query()
            ->whereIn('id', $zoneIds)
            ->orderBy('value')
            ->get();
    }

    public function getFilterDirectoratesProperty()
    {
        $zoneIds = app(UserZoneResolver::class)->zoneIdsForUser(Auth::user());

        if ($zoneIds === []) {
            return collect();
        }

        return Directorate::query()
            ->whereIn('zone_id', $zoneIds)
            ->orderBy('name')
            ->get();
    }

    public function getFilterLabsProperty()
    {
        $zoneIds = app(UserZoneResolver::class)->zoneIdsForUser(Auth::user());

        if ($zoneIds === []) {
            return collect();
        }

        $query = Lab::query()
            ->whereIn('zone_id', $zoneIds)
            ->orderBy('name');

        if ($this->filterDirectorateId !== '') {
            $query->where('directorate_id', $this->filterDirectorateId);
        }

        return $query->get();
    }

    public function getZoneLabelProperty(): string
    {
        $zoneIds = app(UserZoneResolver::class)->zoneIdsForUser(Auth::user());

        if ($zoneIds === []) {
            return 'No zone assigned';
        }

        $zones = Zone::query()->whereIn('id', $zoneIds)->orderBy('value')->get();

        if ($zones->count() === 1) {
            $zone = $zones->first();

            return trim(($zone->key ? $zone->key.' — ' : '').($zone->value ?? ''));
        }

        return $zones->count().' zones';
    }

    public function getUnreadNotificationCountProperty(): int
    {
        return LabUserNotification::query()
            ->where('user_id', Auth::id())
            ->unread()
            ->count();
    }

    public function getNotificationsProperty()
    {
        return LabUserNotification::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(25)
            ->get();
    }

    public function render()
    {
        $requests = $this->baseRequestsQuery()
            ->orderByDesc('created_at')
            ->paginate($this->perPage, pageName: 'requestsPage');

        return view('livewire.lab.equipment-requests.equipment-request-manager', [
            'requests' => $requests,
        ]);
    }

    protected function baseRequestsQuery(): Builder
    {
        $query = EquipmentUsageRequest::query()
            ->with([
                'equipment.lab.directorate',
                'equipment.assetLocation.lab.directorate',
                'requester',
                'sampleDetails',
                'zone',
            ]);

        $this->applyZoneScope($query);
        $this->applyTabScope($query, $this->activeTab);
        $this->applyListFilters($query);

        return $query;
    }

    protected function applyZoneScope(Builder $query): void
    {
        $zoneIds = app(UserZoneResolver::class)->zoneIdsForUser(Auth::user());

        if ($zoneIds === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('zone_id', $zoneIds);
    }

    protected function applyTabScope(Builder $query, string $tab): void
    {
        $userId = Auth::id();

        match ($tab) {
            'submitted' => $query
                ->where('status', EquipmentUsageRequest::STATUS_PENDING)
                ->where('requester_id', '!=', $userId),
            'approved' => $query->where('status', EquipmentUsageRequest::STATUS_APPROVED),
            'scheduled_today' => $query
                ->where('status', EquipmentUsageRequest::STATUS_APPROVED)
                ->whereDate('approved_start_at', today()),
            'my_requests' => $query->where('requester_id', $userId),
            'rejected' => $query->whereIn('status', [
                EquipmentUsageRequest::STATUS_REJECTED,
                EquipmentUsageRequest::STATUS_CANCELLED,
            ]),
            default => $query->where('requester_id', $userId),
        };
    }

    protected function applyListFilters(Builder $query): void
    {
        $search = trim($this->search);

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->whereHas('equipment', function (Builder $equipment) use ($search): void {
                    $equipment->where('name', 'like', '%'.$search.'%')
                        ->orWhere('equipment_number', 'like', '%'.$search.'%');
                })
                    ->orWhereHas('requester', fn (Builder $requester) => $requester->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('sampleDetails', fn (Builder $samples) => $samples->where('sample_code', 'like', '%'.$search.'%'));
            });
        }

        if ($this->dateFrom !== '') {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo !== '') {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        if ($this->filterZoneId !== '') {
            $query->where('zone_id', $this->filterZoneId);
        }

        if ($this->filterLabId !== '') {
            $labId = $this->filterLabId;
            $query->whereHas('equipment', function (Builder $equipment) use ($labId): void {
                $equipment->where('lab_id', $labId)
                    ->orWhereHas('assetLocation', fn (Builder $location) => $location->where('lab_id', $labId));
            });
        }

        if ($this->filterDirectorateId !== '') {
            $directorateId = $this->filterDirectorateId;
            $query->whereHas('equipment', function (Builder $equipment) use ($directorateId): void {
                $equipment->whereHas('lab', fn (Builder $lab) => $lab->where('directorate_id', $directorateId))
                    ->orWhereHas('assetLocation.lab', fn (Builder $lab) => $lab->where('directorate_id', $directorateId));
            });
        }
    }

    protected function countForTab(string $tab): int
    {
        $query = EquipmentUsageRequest::query();
        $this->applyZoneScope($query);
        $this->applyTabScope($query, $tab);

        return $query->count();
    }
}
