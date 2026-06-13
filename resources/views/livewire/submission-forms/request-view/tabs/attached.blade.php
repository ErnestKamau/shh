<div class="workflow-board-panel-body flush-top px-0">
    @php
        $trfi = $instance->testRequestFormInstance;
    @endphp

    @if($trfi)
        <div class="card bg-light border-0 mb-4 shadow-none rounded">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start border-bottom pb-2 mb-3">
                    <div>
                        <h6 class="font-weight-bold text-primary mb-1">
                            <i class="mdi mdi-clipboard-text mr-1"></i>
                            {{ $trfi->testRequestForm->name ?? 'Test Request Form' }}
                        </h6>
                        @if($trfi->testRequestForm && $trfi->testRequestForm->code)
                            <span class="badge badge-secondary">{{ $trfi->testRequestForm->code }}</span>
                        @endif
                    </div>
                    <div class="text-right">
                        <span class="text-muted small d-block">
                            <strong>Submitted:</strong> {{ $trfi->created_at->format('Y-m-d H:i') }}
                        </span>
                        @if($trfi->creator)
                            <span class="text-muted small d-block">
                                <strong>By:</strong> {{ $trfi->creator->name }}
                            </span>
                        @endif
                        <span class="text-muted small d-block mt-1">
                            Use <strong>Actions → Generate Test Request Form</strong> to generate and view the report.
                        </span>
                    </div>
                </div>

                @if($trfi->samplingSchedule)
                    <div class="alert alert-info border-0 shadow-none d-flex align-items-center mb-3">
                        <i class="mdi mdi-calendar-clock mr-2" style="font-size: 1.25rem;"></i>
                        <div>
                            <strong>Tied to Sampling Schedule:</strong>
                            <span class="ml-1">{{ $trfi->samplingSchedule->title }}</span>
                            @if($trfi->samplingSchedule->sampling_datetime)
                                <span class="text-muted small ml-2">({{ \Carbon\Carbon::parse($trfi->samplingSchedule->sampling_datetime)->format('Y-m-d H:i') }})</span>
                            @endif
                        </div>
                    </div>
                @endif

                @php
                    $fields = $trfi->testRequestForm ? $trfi->testRequestForm->getFlatFields() : [];
                    $formData = $trfi->form_data ?? [];
                @endphp

                @if(!empty($fields))
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 40%;">Field Label</th>
                                    <th style="width: 60%;">Captured Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fields as $field)
                                    @if(!empty($field['name']))
                                        @php
                                            $val = $formData[$field['name']] ?? '—';
                                            if (is_bool($val)) {
                                                $val = $val ? 'Yes' : 'No';
                                            }
                                        @endphp
                                        <tr>
                                            <td class="font-weight-bold text-secondary">{{ $field['label'] ?? $field['name'] }}</td>
                                            <td>
                                                @if(is_array($val))
                                                    @php
                                                        $isSequential = array_keys($val) === range(0, count($val) - 1);
                                                        $displayVal = $isSequential ? implode(', ', $val) : implode(', ', array_keys(array_filter($val)));
                                                    @endphp
                                                    {{ $displayVal }}
                                                @else
                                                    {{ $val }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <!-- Fallback: Display raw form data keys and values if no template is found -->
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 40%;">Field</th>
                                    <th style="width: 60%;">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($formData as $key => $val)
                                    @php
                                        if (is_bool($val)) {
                                            $val = $val ? 'Yes' : 'No';
                                        }
                                    @endphp
                                    <tr>
                                        <td class="font-weight-bold text-secondary">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                        <td>
                                            @if(is_array($val))
                                                @php
                                                    $isSequential = array_keys($val) === range(0, count($val) - 1);
                                                    $displayVal = $isSequential ? implode(', ', $val) : implode(', ', array_keys(array_filter($val)));
                                                @endphp
                                                {{ $displayVal }}
                                            @else
                                                {{ $val }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if(!empty($formData['sample_rows']))
                    <div class="mt-4">
                        <h6 class="font-weight-bold text-primary mb-3">
                            <i class="mdi mdi-table mr-1"></i> Captured Sample Details
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-striped mb-0">
                                @if(str_contains($trfi->testRequestForm->code ?? '', 'FOOD') || str_contains($trfi->testRequestForm->name ?? '', 'Food'))
                                    <!-- Food Rows Table Header -->
                                    <thead class="bg-secondary text-white text-center small">
                                        <tr>
                                            <th>S. No.</th>
                                            <th>Sample No.</th>
                                            <th>Sample Description</th>
                                            <th>Sampling Point/Location</th>
                                            <th>Qty.</th>
                                            <th>Sample Type</th>
                                            <th>Sample Condition</th>
                                            <th>Dates & Batch</th>
                                            <th>State</th>
                                            <th>Micro/Chem Param</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        @foreach($formData['sample_rows'] as $rowIdx => $row)
                                            <tr>
                                                <td class="text-center font-weight-bold">{{ $rowIdx + 1 }}</td>
                                                <td>{{ $row['sample_no'] ?? '—' }}</td>
                                                <td>{{ $row['sample_description'] ?? '—' }}</td>
                                                <td>{{ $row['sampling_point'] ?? '—' }}</td>
                                                <td class="text-center">{{ $row['qty'] ?? '—' }}</td>
                                                <td>{{ $row['sample_type'] ?? '—' }}</td>
                                                <td>
                                                    {{ $row['sample_condition'] ?? '—' }}
                                                    @if(!empty($row['sample_temp']))
                                                        ({{ $row['sample_temp'] }}°C)
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(!empty($row['production_date']))
                                                        <strong>Prod:</strong> {{ $row['production_date'] }}<br>
                                                    @endif
                                                    @if(!empty($row['expiration_date']))
                                                        <strong>Exp:</strong> {{ $row['expiration_date'] }}<br>
                                                    @endif
                                                    @if(!empty($row['batch_number']))
                                                        <strong>Batch:</strong> {{ $row['batch_number'] }}
                                                    @endif
                                                </td>
                                                <td>{{ $row['state_of_sample'] ?? '—' }}</td>
                                                <td>{{ $row['parameters'] ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                @else
                                    <!-- Water/Waste Water Rows Table Header -->
                                    <thead class="bg-secondary text-white text-center small">
                                        <tr>
                                            <th>S. No.</th>
                                            <th>Sample No.</th>
                                            <th>Sample Description</th>
                                            <th>Location</th>
                                            <th>Qty.</th>
                                            <th>Sampling Point</th>
                                            <th>Field Data (pH, Cl, Temp, Odor, Appearance)</th>
                                            <th>Test Requirements</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        @foreach($formData['sample_rows'] as $rowIdx => $row)
                                            <tr>
                                                <td class="text-center font-weight-bold">{{ $rowIdx + 1 }}</td>
                                                <td>{{ $row['sample_no'] ?? '—' }}</td>
                                                <td>{{ $row['sample_description'] ?? '—' }}</td>
                                                <td>{{ $row['location'] ?? '—' }}</td>
                                                <td class="text-center">{{ $row['qty'] ?? '—' }}</td>
                                                <td>{{ $row['sampling_point'] ?? '—' }}</td>
                                                <td>
                                                    <strong>pH:</strong> {{ $row['ph'] ?? '—' }}<br>
                                                    <strong>Res. Chlorine:</strong> {{ $row['residual_chlorine'] ?? '—' }}<br>
                                                    <strong>Temp:</strong> {{ !empty($row['sample_temp']) ? $row['sample_temp'] . '°C' : '—' }}<br>
                                                    <strong>Odor:</strong> {{ $row['odor'] ?? '—' }}<br>
                                                    <strong>Appearance:</strong> {{ $row['appearance'] ?? '—' }}
                                                </td>
                                                <td>
                                                    @php
                                                        $reqs = [];
                                                        if (!empty($row['microbiology'])) $reqs[] = 'Microbiology';
                                                        if (!empty($row['legionella'])) $reqs[] = 'Legionella';
                                                        if (!empty($row['chemical_analysis'])) $reqs[] = 'Chemical Analysis';
                                                    @endphp
                                                    {{ !empty($reqs) ? implode(', ', $reqs) : '—' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                @endif
                            </table>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    @else
        <p class="text-muted mb-0 py-3">No test request form responses attached to this request.</p>
    @endif
</div>
