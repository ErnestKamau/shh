@extends('layouts.lab.layout.app', ['dataTable' => true])

@section('title2')
    <title>Analyte Progress Tracking - Multi-Stage Tests</title>
@endsection

@section('content2')
    <main>
        <?php
        $items = [
            ['link' => route('lab-home'), 'name' => 'Lab Management', 'icon' => null],
            ['link' => route('stage-headers.index'), 'name' => 'Multi-Stage Tests', 'icon' => null],
            ['link' => route('stage-headers.progress'), 'name' => 'Progress Tracking', 'icon' => null],
        ];
        ?>
        <x-bread-crumb :items="$items"></x-bread-crumb>
        
        <h2 class="p-4">
            <i class="mdi mdi-progress-clock"></i> Analyte Progress Tracking
            <a href="{{ route('stage-headers.index') }}" class="btn btn-secondary btn-sm float-right">
                <i class="mdi mdi-arrow-left"></i> Back to Test Configurations
            </a>
        </h2>

        <div class="p-4">
            <!-- Today's Pending Tests Card -->
            <div class="card">
                <div class="card-header" style="background-color: #f7f7f7; color: #333;">
                    <h5 class="mb-0">
                        <i class="mdi mdi-calendar-today"></i> Today's Pending Tests
                        <span class="badge badge-light">{{ $todaysTests->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($todaysTests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Sample ID</th>
                                        <th>Analyte</th>
                                        <th>Method</th>
                                        <th>Current Stage</th>
                                        <th>Day</th>
                                        <th>Start Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($todaysTests as $progress)
                                        <tr>
                                            <td>
                                                <strong>#{{ $progress->sampleDetail->id ?? 'N/A' }}</strong>
                                            </td>
                                            <td>
                                                <strong>{{ $progress->stageHeader->analyte->name ?? 'N/A' }}</strong>
                                                <br><small class="text-muted">{{ $progress->stageHeader->analyte->code ?? '' }}</small>
                                            </td>
                                            <td>{{ $progress->stageHeader->method->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge" style="background-color: #f7f7f7; color: #333;">{{ $progress->testStage->stage_name ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary">Day {{ $progress->current_day }}</span>
                                            </td>
                                            <td>{{ $progress->start_date ? $progress->start_date->format('M d, Y') : 'Not started' }}</td>
                                            <td>
                                                <span class="badge badge-{{ $progress->status == 'in_progress' ? 'warning' : ($progress->status == 'completed' ? 'success' : 'secondary') }}">
                                                    {{ ucfirst($progress->status) }}
                                                </span>
                                            </td>
                                            <td nowrap>
                                                @if($progress->status == 'in_progress')
                                                    <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#recordResultModal-{{ $progress->id }}">
                                                        <i class="mdi mdi-check"></i> Record Result
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" onclick="cancelTest({{ $progress->id }})">
                                                        <i class="mdi mdi-cancel"></i> Cancel
                                                    </button>
                                                @elseif($progress->status == 'not_started')
                                                    <button class="btn btn-primary btn-sm" onclick="startTest({{ $progress->id }})">
                                                        <i class="mdi mdi-play"></i> Start Test
                                                    </button>
                                                @else
                                                    <span class="text-muted">Completed</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="mdi mdi-information"></i> No tests scheduled for today.
                        </div>
                    @endif
                </div>
            </div>

            <br>

            <!-- Available Test Configurations Card -->
            <div class="card">
                <div class="card-header" style="background-color: #f7f7f7; color: #333;">
                    <h5 class="mb-0">
                        <i class="mdi mdi-flask"></i> Available Test Configurations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Test Name</th>
                                    <th>Analyte</th>
                                    <th>Method</th>
                                    <th>Total Days</th>
                                    <th>Stages</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stageHeaders as $header)
                                    <tr>
                                        <td><strong>{{ $header->name }}</strong></td>
                                        <td>{{ $header->analyte->name ?? 'N/A' }}</td>
                                        <td>{{ $header->method->name ?? 'N/A' }}</td>
                                        <td>{{ $header->total_days }} days</td>
                                        <td>{{ $header->testStages->count() }} stages</td>
                                        <td>
                                            <a href="{{ route('stage-headers.show', $header->id) }}" class="btn btn-info btn-sm">
                                                <i class="mdi mdi-eye"></i> View Stages
                                            </a>
                                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#assignSampleModal-{{ $header->id }}">
                                                <i class="mdi mdi-play"></i> Start Test
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modals for Recording Results -->
    @foreach($todaysTests as $progress)
        @if($progress->status == 'in_progress')
        <div class="modal fade" id="recordResultModal-{{ $progress->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Record Result - {{ $progress->testStage->stage_name }}</h5>
                    </div>
                    <form action="{{ route('stage-headers.record-result', $progress->id) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Current Stage: <strong>{{ $progress->testStage->stage_name }}</strong></label>
                            </div>
                            <div class="form-group">
                                <label>Result:</label>
                                <select class="form-control" name="result" required>
                                    <option value="">Select Result...</option>
                                    <option value="positive">Positive</option>
                                    <option value="negative">Negative</option>
                                    <option value="inconclusive">Inconclusive</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Observations:</label>
                                <textarea class="form-control" name="observations" rows="3" placeholder="Any observations..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Result</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    @endforeach

    <!-- Modals for Assigning Samples -->
    @foreach($stageHeaders as $header)
    <div class="modal fade" id="assignSampleModal-{{ $header->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Start Test - {{ $header->name }}</h5>
                </div>
                <form action="{{ route('stage-headers.start-test', $header->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Select Sample:</label>
                            <select class="form-control" name="sample_detail_id" required>
                                <option value="">Select Sample...</option>
                                @foreach($availableSamples as $sample)
                                    <option value="{{ $sample->id }}">
                                        Sample #{{ $sample->id }} - {{ $sample->getSampleHeader()->sample_description ?? 'Unknown' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Start Date:</label>
                            <input type="datetime-local" class="form-control" name="start_date" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Start Test</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
@endsection

@section('script2')
<script>
    function startTest(progressId) {
        if (confirm('Are you sure you want to start this test?')) {
            fetch(`/lab/stage-headers/${progressId}/start`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error starting test');
                console.error(error);
            });
        }
    }

    function cancelTest(progressId) {
        if (confirm('Are you sure you want to cancel this test? This action cannot be undone.')) {
            fetch(`/lab/stage-headers/${progressId}/cancel`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error cancelling test');
                console.error(error);
            });
        }
    }

    // Initialize DataTable
    $(document).ready(function() {
        $('table').DataTable({
            pageLength: 25,
            order: [[0, 'desc']]
        });
    });
</script>
@endsection