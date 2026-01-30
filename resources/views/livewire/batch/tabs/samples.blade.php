<div wire:ignore>
@if(isset($batch->sample_detail_processed) && $batch->sample_detail_processed == 0 && isset($batch->stagingDetails) && $batch->stagingDetails->count() > 0)
   <div class="card mb-4">
        <div class="card-header" style="background: linear-gradient(135deg, #fff3cd, #ffeaa7);">
            <h5 class="mb-0"><i class="mdi mdi-clipboard-alert"></i> Unprocessed Staging Data</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="bg-light">
                        <tr>
                            <th>Actions</th>
                            <th>Specimen Type</th>
                            <th>Company Sub Unit</th>
                            <th>Analysis Types</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($batch->stagingDetails as $staging)
                            @if(!$staging->is_processed)
                                <tr>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary assign-samples-btn" 
                                                data-staging-id="{{ $staging->id }}"
                                                data-header-id="{{ $batch->id }}">
                                            <i class="mdi mdi-checkbox-multiple-marked"></i> Assign Samples
                                        </button>
                                        <button type="button" class="btn btn-sm btn-info edit-staging-btn" 
                                                data-staging-id="{{ $staging->id }}"
                                                data-staging-json="{{ json_encode($staging->data_json) }}"
                                                data-toggle="modal"
                                                data-target="#edit-staging-modal">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-staging-btn" 
                                                data-staging-id="{{ $staging->id }}"
                                                data-toggle="modal"
                                                data-target="#delete-staging-modal">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    </td>
                                    <td>{{ $batch->sample_type->name ?? 'N/A' }}</td>
                                    <td>{{ $staging->data_json['company_sub_unit_name'] ?? 'N/A' }}</td>
                                    <td>{{ $staging->data_json['analysis_type_names'] ?? 'N/A' }}</td>
                                    <td>{{ $staging->data_json['quantity'] ?? 1 }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

{{-- Show Assigned Samples when sample_detail_processed is 1 or when there's no staging --}}
@if(!isset($batch->sample_detail_processed) || $batch->sample_detail_processed == 1)
    <h5 class="card-title">
        <span class="btn btn-transparent">Samples Configuration</span>
    </h5>

    @if($batch->samples && $batch->samples->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="bg-light">
                    <tr>
                        <th>Sample Code</th>
                        <th>Description</th>
                        <th>Analysis Types</th>
                        <th>Time Sampled</th>
                        <th>Standard</th>
                        <th>Storage</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batch->samples as $sample)
                        <tr>
                            <td><strong>{{ $sample->sample_code }}</strong></td>
                            <td>{{ $sample->description ?? '-' }}</td>
                            <td>
                                @php
                                    $analysisTypes = explode(',', $sample->analysis_type_id);
                                    $analysisNames = [];
                                    foreach($analysisTypes as $typeId) {
                                        $type = \App\AnalysisType::find($typeId);
                                        if($type) {
                                            $analysisNames[] = $type->name;
                                        }
                                    }
                                @endphp
                                {{ implode(', ', $analysisNames) }}
                            </td>
                            <td>{{ $sample->time_sampled ?? '-' }}</td>
                            <td>
                                @if($sample->main_standard)
                                    {{ \App\Standards::find($sample->main_standard)->code ?? 'N/A' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $sample->storage_location ?? '-' }}</td>
                            <td>{{ $sample->sample_quantity ?? '-' }} {{ $sample->unit_of_measure ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="alert alert-info mt-3">
            <i class="mdi mdi-information"></i> No samples have been assigned yet.
        </div>
    @endif
@endif
</div>
