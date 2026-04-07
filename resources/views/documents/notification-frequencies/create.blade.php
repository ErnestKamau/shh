@extends('layouts.documents.layout.app')

@section('title2')
    <title>Add Notification Frequency - Imara LIMS</title>
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
            'name' => 'Add Frequency',
            'icon' => null
        ),
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">
                <i class="mdi mdi-bell-plus"></i> Add Notification Frequency
            </h2>
            <p class="text-muted mb-0">Create a new notification frequency for document expiry alerts</p>
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

                    <form action="{{ route('documents.notification-frequencies.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Frequency Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
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
                                           value="{{ old('days_interval', 1) }}" 
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
                                      placeholder="Optional description of when this frequency should be used">{{ old('description') }}</textarea>
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
                                       {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
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
                                <i class="mdi mdi-content-save"></i> Create Frequency
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Help Card -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="mdi mdi-help-circle"></i> Frequency Guidelines
                    </h5>
                </div>
                <div class="card-body">
                    <h6><i class="mdi mdi-lightbulb text-warning"></i> Common Frequencies</h6>
                    <ul class="list-unstyled">
                        <li><strong>Daily (1 day):</strong> For critical documents</li>
                        <li><strong>Weekly (7 days):</strong> For standard documents</li>
                        <li><strong>Bi-weekly (14 days):</strong> For less urgent documents</li>
                        <li><strong>Monthly (30 days):</strong> For reference documents</li>
                    </ul>

                    <hr>

                    <h6><i class="mdi mdi-information text-primary"></i> How It Works</h6>
                    <p class="small">
                        When a document is created with this frequency, notifications will be sent:
                    </p>
                    <ul class="small">
                        <li>Every <span id="interval-display">1</span> day(s) before expiry</li>
                        <li>Daily after expiry until updated</li>
                        <li>Only to the document creator</li>
                    </ul>
                </div>
            </div>

            <!-- Quick Add Examples -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="mdi mdi-lightning-bolt"></i> Quick Add Examples
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillForm('Daily', 1, 'Send notifications every day')">
                            <i class="mdi mdi-calendar-day"></i> Daily
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillForm('Weekly', 7, 'Send notifications every week')">
                            <i class="mdi mdi-calendar-week"></i> Weekly
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillForm('Bi-weekly', 14, 'Send notifications every two weeks')">
                            <i class="mdi mdi-calendar-blank"></i> Bi-weekly
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="fillForm('Monthly', 30, 'Send notifications every month')">
                            <i class="mdi mdi-calendar-month"></i> Monthly
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
