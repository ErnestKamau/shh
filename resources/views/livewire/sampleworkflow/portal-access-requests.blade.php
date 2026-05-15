<div style="display: inline-block;" wire:init="loadRequests" wire:ignore>
    <button id="portal-access-requests-btn" type="button" class="btn btn-sm btn-primary btn-action-sm" onclick="$('#portal-access-requests-modal').modal('show')" wire:key="portal-access-btn">
        <i class="mdi mdi-account-plus-outline"></i> Request Account Access Forms
    </button>

    <div class="modal fade" id="portal-access-requests-modal" tabindex="-1" role="dialog" aria-labelledby="portal-access-requests-modal-label" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="portal-access-requests-modal-label">
                        <i class="mdi mdi-account-plus-outline mr-1"></i> Request Account Access Forms
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if(session()->has('message'))
                        <div class="alert alert-success py-2">{{ session('message') }}</div>
                    @endif
                    @if(session()->has('error'))
                        <div class="alert alert-danger py-2">{{ session('error') }}</div>
                    @endif
                    @if($fetchError)
                        <div class="alert alert-warning py-2">
                            <i class="mdi mdi-alert-circle-outline"></i> {{ $fetchError }}
                        </div>
                    @endif

                    @if(!$readyToLoad)
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Fetching account access requests...</p>
                        </div>
                    @elseif($portalAccessRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>Actions</th>
                                        <th>Status</th>
                                        <th>Submitted At</th>
                                        <th>Full Name / Organisation</th>
                                        <th>Address</th>
                                        <th>Zone</th>
                                        <th>TIN Number</th>
                                        <th>Email</th>
                                        <th>Phone Number</th>
                                        <th>Postal Code</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($portalAccessRequests as $accessRequest)
                                        <tr>
                                            <td nowrap>
                                                @if($accessRequest->status == 'pending')
                                                    <button class="btn btn-sm btn-outline-success" wire:click="approvePortalAccessRequest('{{ $accessRequest->id }}')" wire:confirm="Approve this account access request?">
                                                        <i class="mdi mdi-check"></i> Approve
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" wire:click="prepareRejectPortalAccessRequest('{{ $accessRequest->id }}')" data-toggle="modal" data-target="#portal-access-request-reject-modal">
                                                        <i class="mdi mdi-close"></i> Reject
                                                    </button>
                                                @else
                                                    <span class="text-muted">Reviewed</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="workflow-status-chip" style="--chip-accent: {{ $accessRequest->status == 'approved' ? '#28a745' : ($accessRequest->status == 'rejected' ? '#dc3545' : '#ffc107') }};">
                                                    {{ ucfirst($accessRequest->status) }}
                                                </span>
                                            </td>
                                            <td>{{ optional($accessRequest->created_at)->format('Y-m-d H:i') }}</td>
                                            <td>{{ $accessRequest->full_name_or_organisation }}</td>
                                            <td>{{ $accessRequest->address }}</td>
                                            <td>{{ $accessRequest->zone }}</td>
                                            <td>{{ $accessRequest->tin_number }}</td>
                                            <td>{{ $accessRequest->email }}</td>
                                            <td>{{ $accessRequest->phone_number }}</td>
                                            <td>{{ $accessRequest->postal_code }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            {{ $portalAccessRequests->links() }}
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">No account access requests have been submitted yet.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="portal-access-request-reject-modal" tabindex="-1" role="dialog" aria-labelledby="portal-access-request-reject-modal-label" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="portal-access-request-reject-modal-label">
                        <i class="mdi mdi-alert-outline mr-1"></i> Reject Access Request
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="notifyRejectedClient" wire:model="notifyRejectedClient">
                            <label class="custom-control-label" for="notifyRejectedClient">Notify client by email</label>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="portalRejectionReason">Reason (optional)</label>
                        <textarea id="portalRejectionReason" class="form-control" rows="3" wire:model="portalRejectionReason" placeholder="Enter rejection reason"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="confirmRejectPortalAccessRequest" data-dismiss="modal">
                        <i class="mdi mdi-close mr-1"></i> Confirm Reject
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            console.log('PortalAccessRequests initialized');
        });
    </script>
</div>
