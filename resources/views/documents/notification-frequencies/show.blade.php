@extends('layouts.documents.layout.app')

@section('title2')
    <title>Notification Frequency Details - Imara LIMS</title>
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
            'name' => $notificationFrequency->name,
            'icon' => null
        ),
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">
                <i class="mdi mdi-bell"></i> {{ $notificationFrequency->name }}
            </h2>
            <p class="text-muted mb-0">Notification frequency details and usage information</p>
        </div>
        <div>
            <a href="{{ route('documents.notification-frequencies.edit', $notificationFrequency->id) }}" class="btn btn-warning">
                <i class="mdi mdi-pencil"></i> Edit
            </a>
            <a href="{{ route('documents.notification-frequencies.index') }}" class="btn btn-secondary">
                <i class="mdi mdi-arrow-left"></i> Back to Frequencies
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Frequency Details Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="mdi mdi-bell-outline"></i> Frequency Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $notificationFrequency->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Days Interval:</strong></td>
                                    <td>
                                        <span class="badge bg-info fs-6">{{ $notificationFrequency->days_interval }} days</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($notificationFrequency->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $notificationFrequency->created_at->format('M d, Y \a\t g:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $notificationFrequency->updated_at->format('M d, Y \a\t g:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Documents Using:</strong></td>
                                    <td>
                                        <span class="badge bg-primary fs-6">{{ $notificationFrequency->documents_count ?? 0 }} documents</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($notificationFrequency->description)
                        <hr>
                        <div>
                            <h6><i class="mdi mdi-text"></i> Description</h6>
                            <p class="mb-0">{{ $notificationFrequency->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- How It Works Card -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="mdi mdi-information"></i> How This Frequency Works
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="mdi mdi-clock-outline text-primary"></i> Before Document Expiry</h6>
                            <ul class="list-unstyled">
                                <li><i class="mdi mdi-check text-success"></i> Notifications sent every {{ $notificationFrequency->days_interval }} day(s)</li>
                                <li><i class="mdi mdi-check text-success"></i> Starts 30 days before expiry (configurable)</li>
                                <li><i class="mdi mdi-check text-success"></i> Sent to document creator only</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="mdi mdi-alert text-warning"></i> After Document Expiry</h6>
                            <ul class="list-unstyled">
                                <li><i class="mdi mdi-check text-success"></i> Daily notifications until document is updated</li>
                                <li><i class="mdi mdi-check text-success"></i> Continues until notifications are disabled</li>
                                <li><i class="mdi mdi-check text-success"></i> Sent to document creator only</li>
                            </ul>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <h6><i class="mdi mdi-lightbulb"></i> Example Timeline</h6>
                        <p class="mb-0">
                            For a document expiring on <strong>December 31, 2024</strong> with this frequency:
                        </p>
                        <ul class="mb-0 mt-2">
                            <li><strong>December 1:</strong> First notification sent</li>
                            <li><strong>December {{ 1 + $notificationFrequency->days_interval }}:</strong> Second notification sent</li>
                            <li><strong>December {{ 1 + ($notificationFrequency->days_interval * 2) }}:</strong> Third notification sent</li>
                            <li><strong>December 31:</strong> Document expires</li>
                            <li><strong>January 1 onwards:</strong> Daily notifications until updated</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Usage Statistics -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="mdi mdi-chart-bar"></i> Usage Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="display-4 text-primary">{{ $notificationFrequency->documents_count ?? 0 }}</div>
                        <div class="text-muted">Documents Using This Frequency</div>
                    </div>

                    @if(($notificationFrequency->documents_count ?? 0) > 0)
                        <div class="alert alert-success">
                            <i class="mdi mdi-check-circle"></i>
                            <strong>Active Usage:</strong> This frequency is currently being used by documents.
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="mdi mdi-alert"></i>
                            <strong>No Usage:</strong> This frequency is not currently being used by any documents.
                        </div>
                    @endif

                    <hr>

                    <h6><i class="mdi mdi-calendar-check text-success"></i> Recent Activity</h6>
                    <ul class="list-unstyled">
                        <li><i class="mdi mdi-circle-small text-primary"></i> Created: {{ $notificationFrequency->created_at->diffForHumans() }}</li>
                        <li><i class="mdi mdi-circle-small text-primary"></i> Updated: {{ $notificationFrequency->updated_at->diffForHumans() }}</li>
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
                        <a href="{{ route('documents.notification-frequencies.edit', $notificationFrequency->id) }}" 
                           class="btn btn-outline-warning">
                            <i class="mdi mdi-pencil"></i> Edit Frequency
                        </a>
                        
                        @if(($notificationFrequency->documents_count ?? 0) == 0)
                            <form action="{{ route('documents.notification-frequencies.destroy', $notificationFrequency->id) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this frequency?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="mdi mdi-delete"></i> Delete Frequency
                                </button>
                            </form>
                        @else
                            <button class="btn btn-outline-secondary w-100" disabled 
                                    title="Cannot delete - in use by documents">
                                <i class="mdi mdi-delete"></i> Delete Frequency
                            </button>
                        @endif
                        
                        <a href="{{ route('documents.notification-frequencies.index') }}" 
                           class="btn btn-outline-secondary">
                            <i class="mdi mdi-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>

            <!-- Similar Frequencies -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="mdi mdi-bell-multiple"></i> Similar Frequencies
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $similarFrequencies = \App\Models\NotificationFrequency::where('id', '!=', $notificationFrequency->id)
                            ->where('days_interval', $notificationFrequency->days_interval)
                            ->limit(3)
                            ->get();
                    @endphp
                    
                    @if($similarFrequencies->count() > 0)
                        <ul class="list-unstyled">
                            @foreach($similarFrequencies as $similar)
                                <li class="mb-2">
                                    <a href="{{ route('documents.notification-frequencies.show', $similar->id) }}" 
                                       class="text-decoration-none">
                                        <i class="mdi mdi-bell text-primary"></i>
                                        {{ $similar->name }}
                                        <small class="text-muted">({{ $similar->days_interval }} days)</small>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted small mb-0">No similar frequencies found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
