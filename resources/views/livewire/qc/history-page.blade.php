<div class="qc-page">
    @include('livewire.qc._shared-styles')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0"><i class="mdi mdi-history mr-1"></i> QC History</h5>
            <small class="text-muted">Filter and review QC result history.</small>
        </div>
        <button type="button" class="btn btn-sm btn-light" wire:click="clearFilters">Clear Filters</button>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-2"><label>Start Date</label><input type="date" wire:model.live="startDate" class="form-control form-control-sm"></div>
                <div class="form-group col-md-2"><label>End Date</label><input type="date" wire:model.live="endDate" class="form-control form-control-sm"></div>
                <div class="form-group col-md-2">
                    <label>QC Type</label>
                    <select wire:model.live="qcTypeId" class="form-control form-control-sm">
                        <option value="">All</option>
                        @foreach($qcTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>QC Scheme</label>
                    <select wire:model.live="qcSchemeId" class="form-control form-control-sm">
                        <option value="">All</option>
                        @foreach($qcSchemes as $scheme)
                            <option value="{{ $scheme->id }}">{{ $scheme->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>Standard</label>
                    <select wire:model.live="standardId" class="form-control form-control-sm">
                        <option value="">All</option>
                        @foreach($standards as $standard)
                            <option value="{{ $standard->id }}">{{ $standard->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>Sample Type</label>
                    <select wire:model.live="sampleTypeId" class="form-control form-control-sm">
                        <option value="">All</option>
                        @foreach($sampleTypes as $sampleType)
                            <option value="{{ $sampleType->id }}">{{ $sampleType->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Analysis Type</label>
                    <select wire:model.live="analysisTypeId" class="form-control form-control-sm">
                        <option value="">All</option>
                        @foreach($analysisTypes as $analysisType)
                            <option value="{{ $analysisType->id }}">{{ $analysisType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Analyte</label>
                    <select wire:model.live="analyteId" class="form-control form-control-sm">
                        <option value="">All</option>
                        @foreach($analytes as $analyte)
                            <option value="{{ $analyte->id }}">{{ $analyte->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>Remark</label>
                    <select wire:model.live="remark" class="form-control form-control-sm">
                        <option value="">All</option>
                        <option value="PASS">Pass</option>
                        <option value="FAIL">Fail</option>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>Group By</label>
                    <select wire:model.live="groupBy" class="form-control form-control-sm">
                        <option value="1">By Parameter</option>
                        <option value="2">By Sample</option>
                        <option value="3">By Batch</option>
                    </select>
                </div>
                <div class="form-group col-md-2 d-flex align-items-end">
                    <div class="text-muted small">Showing {{ $results->count() }} rows</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card qc-table-card">
        <div class="card-body">
            <div class="table-responsive qc-table-wrap">
                <table class="table table-sm table-bordered table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Receipt Date</th>
                        <th>Batch Code</th>
                        <th>Sample Code</th>
                        @if($groupBy === '1')
                            <th>Analyte</th>
                        @endif
                        <th>Sample Type</th>
                        <th>Result</th>
                        @if(isset($selectedQcType?->id) && $selectedQcType->use_existing_sample)
                            @if($groupBy === '1')
                                <th>Previous Result</th>
                            @endif
                            <th>+- %</th>
                        @endif
                        @if($groupBy === '1')
                            <th>Standard</th>
                        @endif
                        <th>Remark</th>
                        <th>Analyst</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $row)
                        <tr>
                            <td>{{ $row->receipt_date }}</td>
                            <td>{{ $row->batch_code }}</td>
                            <td>{{ $row->sample_detail_code }}</td>
                            @if($groupBy === '1')
                                <td>{{ $row->analyte_code }}</td>
                            @endif
                            <td>{{ $row->sample_type_name }}</td>
                            <td>{{ $row->result }}</td>
                            @if(isset($selectedQcType?->id) && $selectedQcType->use_existing_sample)
                                @if($groupBy === '1')
                                    <td>{{ $row->previous_result }}</td>
                                @endif
                                <td>{{ $row->config_percentage }}</td>
                            @endif
                            @if($groupBy === '1')
                                <td>{{ $row->main_value }}</td>
                            @endif
                            <td>{{ $row->remarks }}</td>
                            <td>{{ $row->analyst_name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="12" class="text-center text-muted">No QC history records found.</td></tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
