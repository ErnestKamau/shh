<?php

namespace App\Livewire\Sampleworkflow;

use App\Models\PortalAccessRequest;
use App\Services\Portal\PortalAccessInvitationService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class PortalAccessRequests extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public ?string $selectedPortalAccessRequestId = null;
    public bool $notifyRejectedClient = true;
    public string $portalRejectionReason = '';
    public ?string $fetchError = null;

    public bool $readyToLoad = false;

    public function loadRequests()
    {
        $this->readyToLoad = true;
    }

    public function render()
    {
        return view('livewire.sampleworkflow.portal-access-requests', [
            'portalAccessRequests' => $this->readyToLoad ? $this->portalAccessRequests : collect(),
        ]);
    }

    /**
     * Computed property to fetch access requests from the portal API.
     */
    public function getPortalAccessRequestsProperty()
    {
        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage('access_requests_page');

        $this->fetchError = null;
        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'X-Relay-Key' => $this->portalRelaySharedKey(),
                    'Accept' => 'application/json',
                ])
                ->get($this->portalAuthApiBaseUrl().'/api/v1/auth/access-requests', [
                    'page' => $page,
                    'per_page' => $perPage,
                ]);

            if ($response->failed()) {
                $this->fetchError = 'Portal API returned error: ' . ($response->json('message') ?? $response->status());
                return $this->emptyPortalAccessRequestsPaginator($perPage, $page);
            }

            $data = (array) $response->json();
            $rows = collect((array) ($data['data'] ?? []))
                ->map(function ($row) {
                    $row = (array) $row;
                    if (! empty($row['created_at'])) {
                        $row['created_at'] = Carbon::parse((string) $row['created_at']);
                    }
                    if (! empty($row['reviewed_at'])) {
                        $row['reviewed_at'] = Carbon::parse((string) $row['reviewed_at']);
                    }
                    return (object) $row;
                });

            $meta = (array) ($data['meta'] ?? []);
            $total = (int) ($meta['total'] ?? $rows->count());

            return new LengthAwarePaginator(
                $rows,
                $total,
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'pageName' => 'access_requests_page',
                ]
            );
        } catch (Throwable $e) {
            report($e);
            $this->fetchError = 'Failed to connect to Portal API: ' . $e->getMessage();
            return $this->emptyPortalAccessRequestsPaginator($perPage, $page);
        }
    }

    public function approvePortalAccessRequest(string $id): void
    {
        $requestData = $this->fetchPortalAccessRequest($id);

        if (! $requestData) {
            session()->flash('error', 'Access request not found in portal API.');
            return;
        }

        if (($requestData['status'] ?? '') !== 'pending') {
            session()->flash('error', 'This access request has already been reviewed.');
            return;
        }

        try {
            $request = new PortalAccessRequest([
                'full_name_or_organisation_enc' => (string) ($requestData['full_name_or_organisation'] ?? ''),
                'address_enc' => (string) ($requestData['address'] ?? ''),
                'zone' => (string) ($requestData['zone'] ?? ''),
                'tin_number_enc' => (string) ($requestData['tin_number'] ?? ''),
                'email_enc' => (string) ($requestData['email'] ?? ''),
                'phone_number_enc' => (string) ($requestData['phone_number'] ?? ''),
                'postal_code' => (string) ($requestData['postal_code'] ?? ''),
                'status' => (string) ($requestData['status'] ?? 'pending'),
            ]);

            $service = app(PortalAccessInvitationService::class);
            $invite = $service->createInvite($request, Auth::id());

            if (trim((string) ($invite['invitation_url'] ?? '')) === '') {
                throw new \RuntimeException('Invite link was not generated.');
            }

            $service->sendApprovalEmail(
                (string) $request->email,
                (string) $request->full_name_or_organisation,
                (string) $invite['invitation_url'],
            );

            $marked = Http::timeout(20)
                ->acceptJson()
                ->withHeaders(['X-Relay-Key' => $this->portalRelaySharedKey()])
                ->patch($this->portalAuthApiBaseUrl().'/api/v1/auth/access-requests/'.urlencode($id).'/approve', [
                    'review_notes' => 'Invite link sent to client email.',
                ]);

            if ($marked->failed()) {
                throw new \RuntimeException((string) ($marked->json('message') ?? 'Failed to mark access request as approved.'));
            }

            session()->flash('message', 'Access request approved and invite link sent successfully.');
        } catch (Throwable $e) {
            report($e);
            session()->flash('error', 'Failed to approve request and send invite. '.$e->getMessage());
        }
    }

    public function prepareRejectPortalAccessRequest(string $id): void
    {
        $request = $this->fetchPortalAccessRequest($id);

        if (! $request) {
            session()->flash('error', 'Access request not found in portal API.');
            return;
        }

        if (($request['status'] ?? '') !== 'pending') {
            session()->flash('error', 'This access request has already been reviewed.');
            return;
        }

        $this->selectedPortalAccessRequestId = (string) ($request['id'] ?? '');
        $this->notifyRejectedClient = true;
        $this->portalRejectionReason = '';
    }

    public function confirmRejectPortalAccessRequest(): void
    {
        if (! $this->selectedPortalAccessRequestId) {
            session()->flash('error', 'No access request selected for rejection.');
            return;
        }

        $requestData = $this->fetchPortalAccessRequest($this->selectedPortalAccessRequestId);

        if (! $requestData) {
            session()->flash('error', 'Access request not found in portal API.');
            return;
        }

        if (($requestData['status'] ?? '') !== 'pending') {
            session()->flash('error', 'This access request has already been reviewed.');
            return;
        }

        try {
            if ($this->notifyRejectedClient) {
                $request = new PortalAccessRequest([
                    'full_name_or_organisation_enc' => (string) ($requestData['full_name_or_organisation'] ?? ''),
                    'email_enc' => (string) ($requestData['email'] ?? ''),
                ]);

                app(PortalAccessInvitationService::class)->sendRejectionEmail(
                    (string) $request->email,
                    (string) $request->full_name_or_organisation,
                    $this->portalRejectionReason,
                );
            }

            $marked = Http::timeout(20)
                ->acceptJson()
                ->withHeaders(['X-Relay-Key' => $this->portalRelaySharedKey()])
                ->patch($this->portalAuthApiBaseUrl().'/api/v1/auth/access-requests/'.urlencode($this->selectedPortalAccessRequestId).'/reject', [
                    'review_notes' => $this->portalRejectionReason ?: null,
                ]);

            if ($marked->failed()) {
                throw new \RuntimeException((string) ($marked->json('message') ?? 'Failed to mark access request as rejected.'));
            }

            $this->selectedPortalAccessRequestId = null;
            $this->notifyRejectedClient = true;
            $this->portalRejectionReason = '';

            session()->flash('message', 'Access request rejected successfully.');
        } catch (Throwable $e) {
            report($e);
            session()->flash('error', 'Failed to reject request. '.$e->getMessage());
        }
    }

    protected function emptyPortalAccessRequestsPaginator(int $perPage, int $page): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            collect(),
            0,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'access_requests_page',
            ]
        );
    }

    protected function fetchPortalAccessRequest(string $id): ?array
    {
        $response = Http::timeout(20)
            ->acceptJson()
            ->withHeaders(['X-Relay-Key' => $this->portalRelaySharedKey()])
            ->get($this->portalAuthApiBaseUrl().'/api/v1/auth/access-requests/'.urlencode($id));

        if ($response->failed()) {
            return null;
        }

        $data = (array) $response->json('data', []);

        return $data === [] ? null : $data;
    }

    protected function portalAuthApiBaseUrl(): string
    {
        return rtrim((string) (config('services.portal_relay.auth_api_base_url') ?: config('app.url')), '/');
    }

    protected function portalRelaySharedKey(): string
    {
        return (string) config('services.portal_relay.shared_key');
    }
}
