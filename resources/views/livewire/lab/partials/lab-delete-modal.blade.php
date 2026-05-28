@php
    $confirmMethod = $confirmMethod ?? 'deleteLab';
    $entityLabel = match ($deleteType) {
        'lab_section' => 'Lab Section',
        'decontamination_area' => 'Decontamination Area',
        default => 'Lab',
    };
@endphp
@if($showDeleteModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="mdi mdi-alert-circle"></i>
                        Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeDeleteModal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert"></i>
                        <strong>Warning:</strong> This action cannot be undone.
                    </div>

                    <p class="mb-3">Are you sure you want to delete the following <strong>{{ $entityLabel }}</strong>?</p>

                    <div class="card">
                        <div class="card-body bg-light">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <th style="width: 35%;">Name:</th>
                                    <td><strong>{{ $deleteDetails['name'] ?? '-' }}</strong></td>
                                </tr>
                                @if($deleteType === 'lab_section')
                                    <tr>
                                        <th>Code:</th>
                                        <td>{{ $deleteDetails['code'] ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Environmental Monitoring:</th>
                                        <td>{{ $deleteDetails['environmental_monitoring'] ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Equipment:</th>
                                        <td>{{ $deleteDetails['equipment'] ?? '-' }}</td>
                                    </tr>
                                @elseif($deleteType === 'decontamination_area')
                                    <tr>
                                        <th>Lab Section:</th>
                                        <td>{{ $deleteDetails['lab_section'] ?? '-' }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <th>Code:</th>
                                        <td>{{ $deleteDetails['code'] ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td>{{ $deleteDetails['email'] ?? '-' }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Status:</th>
                                    <td>{{ $deleteDetails['active'] ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeDeleteModal">
                        <i class="mdi mdi-close"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" wire:click="{{ $confirmMethod }}">
                        <i class="mdi mdi-delete"></i> Yes, Delete Permanently
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
