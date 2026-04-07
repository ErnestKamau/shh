@extends('layouts.documents.layout.app')

@section('title2')
    <title>Edit Notification Frequency - Imara LIMS</title>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => '/home',
            'name' => 'Home',
            'icon' => null
        ),
        array(
            'link' => '/documents/dashboard',
            'name' => 'Documents',
            'icon' => null
        ),
        array(
            'link' => '/documents/notification-frequencies',
            'name' => 'Notification Frequencies',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => 'Edit Frequency',
            'icon' => null
        ),
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">
                <i class="mdi mdi-bell-edit"></i> Edit Notification Frequency
            </h2>
            <p class="text-muted mb-0">Modify the notification frequency settings</p>
        </div>
        <div>
            <a href="{{ route('documents.notification-frequencies.index') }}" class="btn btn-secondary">
                <i class="mdi mdi-arrow-left"></i> Back to Frequencies
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="mdi mdi-bell-outline"></i> Frequency Details
                    </h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-alert-circle me-2"></i>
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('documents.notification-frequencies.update', $notificationFrequency->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Frequency Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $notificationFrequency->name) }}" 
                                           placeholder="e.g., Daily, Weekly, Monthly"
                                           required>
                                    <div class="form-text">A descriptive name for this frequency</div>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="days_interval" class="form-label">Days Interval <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('days_interval') is-invalid @enderror" 
                                           id="days_interval" 
                                           name="days_interval" 
                                           value="{{ old('days_interval', $notificationFrequency->days_interval) }}" 
                                           min="1" 
                                           max="365"
                                           required>
                                    <div class="form-text">How often to send notifications (in days)</div>
                                    @error('days_interval')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3" 
                                      placeholder="Optional description of when this frequency should be used">{{ old('description', $notificationFrequency->description) }}</textarea>
                            <div class="form-text">Explain when this frequency is most appropriate</div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input @error('is_active') is-invalid @enderror" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', $notificationFrequency->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active</strong>
                                </label>
                                <div class="form-text">Enable this frequency for use in document creation</div>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('documents.notification-frequencies.index') }}" class="btn btn-secondary">
                                <i class="mdi mdi-cancel"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save"></i> Update Frequency
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Usage Information -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="mdi mdi-information"></i> Usage Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6><i class="mdi mdi-file-document text-primary"></i> Documents Using This Frequency</h6>
                        <p class="mb-2">
                            <span class="badge bg-primary fs-6">{{ $notificationFrequency->documents_count ?? 0 }} documents</span>
                        </p>
                        @if(($notificationFrequency->documents_count ?? 0) > 0)
                            <div class="alert alert-warning">
                                <i class="mdi mdi-alert"></i>
                                <strong>Note:</strong> Changes to this frequency will affect all documents currently using it.
                            </div>
                        @else
                            <div class="alert alert-success">
                                <i class="mdi mdi-check-circle"></i>
                                <strong>Safe to edit:</strong> No documents are currently using this frequency.
                            </div>
                        @endif
                    </div>

                    <hr>

                    <h6><i class="mdi mdi-clock-outline text-warning"></i> Current Settings</h6>
                    <ul class="list-unstyled">
                        <li><strong>Name:</strong> {{ $notificationFrequency->name }}</li>
                        <li><strong>Interval:</strong> {{ $notificationFrequency->days_interval }} days</li>
                        <li><strong>Status:</strong> 
                            @if($notificationFrequency->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </li>
                        <li><strong>Created:</strong> {{ $notificationFrequency->created_at->format('M d, Y') }}</li>
                        <li><strong>Updated:</strong> {{ $notificationFrequency->updated_at->format('M d, Y') }}</li>
                    </ul>
                </div>
            </div>

            <!-- How It Works -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="mdi mdi-help-circle"></i> How It Works
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small">
                        With this frequency, notifications will be sent:
                    </p>
                    <ul class="small">
                        <li>Every <span id="interval-display">{{ $notificationFrequency->days_interval }}</span> day(s) before expiry</li>
                        <li>Daily after expiry until updated</li>
                        <li>Only to the document creator</li>
                    </ul>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="mdi mdi-lightning-bolt"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillForm('Daily', 1, 'Send notifications every day')">
                            <i class="mdi mdi-calendar-day"></i> Set to Daily
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillForm('Weekly', 7, 'Send notifications every week')">
                            <i class="mdi mdi-calendar-week"></i> Set to Weekly
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillForm('Bi-weekly', 14, 'Send notifications every two weeks')">
                            <i class="mdi mdi-calendar-blank"></i> Set to Bi-weekly
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillForm('Monthly', 30, 'Send notifications every month')">
                            <i class="mdi mdi-calendar-month"></i> Set to Monthly
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function fillForm(name, interval, description) {
    document.getElementById('name').value = name;
    document.getElementById('days_interval').value = interval;
    document.getElementById('description').value = description;
    document.getElementById('interval-display').textContent = interval;
}

// Update interval display when days_interval changes
document.getElementById('days_interval').addEventListener('input', function() {
    document.getElementById('interval-display').textContent = this.value;
});
</script>
@endsection
