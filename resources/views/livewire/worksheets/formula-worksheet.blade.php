<div>
    <!-- Message Alert -->
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    @if($worksheetsReadOnly)
        <div class="alert alert-warning border mb-3">
            <i class="mdi mdi-lock-outline"></i>
            Assign a lab section in your profile before capturing worksheet results. You can view data but cannot save or post.
        </div>
    @elseif($worksheetsSectionFiltered)
        <div class="alert alert-light border mb-3">
            <i class="mdi mdi-flask-outline text-primary"></i>
            Showing worksheet rows for your lab section(s) only.
        </div>
    @endif

    <!-- Formula Info -->
    <div class="alert alert-light border mb-4">
        <div class="d-flex align-items-start">
            <i class="mdi mdi-information mdi-24px text-primary mr-2"></i>
            <div class="flex-grow-1">
                <strong>Formula:</strong> {{ $formula->name }}<br>
                <small class="text-muted">{{ $formula->description }}</small>
                
                @if($this->getUsedLookupTables()->count() > 0)
                    <div class="mt-3 pt-3 border-top">
                        <strong class="text-info">
                            <i class="mdi mdi-table-search"></i> Lookup Tables Used:
                        </strong>
                        <div class="mt-2">
                            @foreach($this->getUsedLookupTables() as $lookupTable)
                                <div class="d-inline-flex align-items-center mr-3 mb-2">
                                    <span class="badge badge-light border p-2">
                                        {{ $lookupTable->name }}
                                    </span>
                                    <a href="{{ route('formulars.lookup-table-entries', $lookupTable) }}" 
                                       target="_blank"
                                       class="btn btn-sm btn-link text-primary ml-1"
                                       title="View lookup table entries">
                                        <i class="mdi mdi-arrow-expand"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Posted Results Status Badge -->
    @php
        $worksheet = $this->getWorksheetWithPostingInfo();
    @endphp
    @if($worksheet && $worksheet->posted_at)
        <div class="alert alert-success border mb-4">
            <div class="d-flex align-items-center">
                <i class="mdi mdi-check-circle mdi-24px text-success mr-2"></i>
                <div>
                    <strong>Results Posted</strong><br>
                    <small class="text-muted">
                        Posted on {{ $worksheet->posted_at->format('M d, Y H:i') }} 
                        by {{ $worksheet->postedBy->name ?? 'Unknown' }}
                    </small>
                </div>
            </div>
        </div>
    @endif

    @if($capturedResults->count() > 0)
        {{-- View Layout Mode Switcher --}}
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <div>
                <h5 class="mb-0 text-muted small">
                    <i class="mdi mdi-eye-outline text-primary mr-1"></i> Layout View Mode
                </h5>
            </div>
            <div class="btn-group shadow-sm" role="group">
                <button type="button" 
                    wire:click="$set('viewMode', 'form')" 
                    class="btn btn-sm {{ $viewMode === 'form' ? 'btn-primary' : 'btn-outline-secondary' }}"
                    style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;">
                    <i class="mdi mdi-card-bulleted-outline"></i> Form Layout
                </button>
                <button type="button" 
                    wire:click="$set('viewMode', 'table')" 
                    class="btn btn-sm {{ $viewMode === 'table' ? 'btn-primary' : 'btn-outline-secondary' }}"
                    style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                    <i class="mdi mdi-table"></i> Tabular Layout
                </button>
            </div>
        </div>

        @if($viewMode === 'table')
            <div class="card border-0 shadow-sm mb-4 fws-card">
                <div class="card-header fws-card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <i class="mdi mdi-table-large text-success mr-2"></i>
                        <span class="font-weight-bold text-dark">Tabular Worksheet Entry</span>
                    </div>
                    <div>
                        @if(! $worksheetsReadOnly)
                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="saveWorksheetLevel" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveWorksheetLevel">
                                <i class="mdi mdi-content-save"></i> Save All
                            </span>
                            <span wire:loading wire:target="saveWorksheetLevel">
                                <i class="mdi mdi-loading mdi-spin"></i> Saving...
                            </span>
                        </button>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0 text-center align-middle" style="font-size: 0.85rem; min-width: 1000px;">
                            <thead class="thead-light">
                                <!-- First Header Row: Sections -->
                                <tr>
                                    <th colspan="7" class="bg-light text-primary font-weight-bold border-bottom">Sample Metadata &amp; Run Details</th>
                                    @if($mandatoryFields->isNotEmpty())
                                        <th colspan="{{ $mandatoryFields->count() }}" class="bg-light text-warning font-weight-bold border-bottom">Mandatory Fields</th>
                                    @endif
                                    @php
                                        $tabularSteps = $formulaSteps->whereIn('step_type', ['input', 'dataset', 'checkbox', 'derived', 'lookup'])->sortBy('step_number');
                                    @endphp
                                    @if($tabularSteps->isNotEmpty())
                                        <th colspan="{{ $tabularSteps->count() }}" class="bg-light text-info font-weight-bold border-bottom">Formula Steps &amp; Measurands</th>
                                    @endif
                                    <th class="bg-light text-success font-weight-bold border-bottom">Result</th>
                                </tr>
                                <!-- Second Header Row: Column Names -->
                                <tr>
                                    <!-- Metadata Columns -->
                                    <th style="min-width: 140px;">Sample Code</th>
                                    <th style="min-width: 130px;">Date</th>
                                    <th style="min-width: 100px;">Time In</th>
                                    <th style="min-width: 150px;">Done By</th>
                                    <th style="min-width: 100px;">Time Out</th>
                                    <th style="min-width: 150px;">Read By</th>
                                    <th style="min-width: 130px;">Read Date</th>
                                    <!-- Mandatory Fields Columns -->
                                    @foreach($mandatoryFields as $field)
                                        <th style="min-width: 140px;">{{ $field->field_label }}</th>
                                    @endforeach
                                    <!-- Steps Columns -->
                                    @foreach($tabularSteps as $step)
                                        <th style="min-width: 140px;">
                                            <div>{{ $step->label }}</div>
                                            <span class="badge badge-light border text-muted small" style="font-size: 0.7rem;">
                                                {{ strtoupper($step->step_type) }}
                                            </span>
                                        </th>
                                    @endforeach
                                    <!-- Result Column -->
                                    <th style="min-width: 110px;">Final Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($capturedResults as $captured)
                                    @php
                                        $crId = (string) $captured->id;
                                    @endphp
                                    <tr wire:key="row-{{ $crId }}">
                                        <!-- Sample Code -->
                                        <td class="font-weight-bold align-middle bg-white">
                                            {{ $captured->sample->sample_code }}
                                        </td>
                                        <!-- Date -->
                                        <td class="align-middle">
                                            <input type="date" class="form-control form-control-sm text-center border-0 bg-transparent"
                                                wire:model.live.debounce.1000ms="worksheetData.{{ $crId }}.date"
                                                wire:blur="saveWorksheet('{{ $crId }}')">
                                        </td>
                                        <!-- Time In -->
                                        <td class="align-middle">
                                            <input type="time" class="form-control form-control-sm text-center border-0 bg-transparent"
                                                wire:model.live.debounce.1000ms="worksheetData.{{ $crId }}.time_in"
                                                wire:blur="saveWorksheet('{{ $crId }}')">
                                        </td>
                                        <!-- Done By -->
                                        <td class="align-middle">
                                            <select class="form-control form-control-sm border-0 bg-transparent text-center"
                                                wire:model.live="worksheetData.{{ $crId }}.done_by_user_id"
                                                wire:change="saveWorksheet('{{ $crId }}')">
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <!-- Time Out -->
                                        <td class="align-middle">
                                            <input type="time" class="form-control form-control-sm text-center border-0 bg-transparent"
                                                wire:model.live.debounce.1000ms="worksheetData.{{ $crId }}.time_out"
                                                wire:blur="saveWorksheet('{{ $crId }}')">
                                        </td>
                                        <!-- Read By -->
                                        <td class="align-middle">
                                            <select class="form-control form-control-sm border-0 bg-transparent text-center"
                                                wire:model.live="worksheetData.{{ $crId }}.read_by_user_id"
                                                wire:change="saveWorksheet('{{ $crId }}')">
                                                <option value="">Select...</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <!-- Read Date -->
                                        <td class="align-middle">
                                            <input type="date" class="form-control form-control-sm text-center border-0 bg-transparent"
                                                wire:model.live.debounce.1000ms="worksheetData.{{ $crId }}.read_date"
                                                wire:blur="saveWorksheet('{{ $crId }}')">
                                        </td>
                                        
                                        <!-- Mandatory Fields -->
                                        @foreach($mandatoryFields as $field)
                                            @php
                                                $fieldId = (string) $field->id;
                                                $fieldType = $field->field_type ?? 'text';
                                            @endphp
                                            <td class="align-middle">
                                                @if($fieldType === 'select')
                                                    <select class="form-control form-control-sm border-0 bg-transparent text-center"
                                                        wire:model.live="worksheetData.{{ $crId }}.mandatory.{{ $fieldId }}"
                                                        wire:change="saveWorksheet('{{ $crId }}')">
                                                        <option value="">Select...</option>
                                                        @foreach(explode(',', $field->select_options ?? '') as $opt)
                                                            @php $opt = trim($opt); @endphp
                                                            @if($opt)
                                                                <option value="{{ $opt }}">{{ $opt }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                @elseif($fieldType === 'date')
                                                    <input type="date" class="form-control form-control-sm border-0 bg-transparent text-center"
                                                        wire:model.live.debounce.1000ms="worksheetData.{{ $crId }}.mandatory.{{ $fieldId }}"
                                                        wire:blur="saveWorksheet('{{ $crId }}')">
                                                @elseif($fieldType === 'checkbox')
                                                    <input type="checkbox" class="form-check-input"
                                                        wire:model.live="worksheetData.{{ $crId }}.mandatory.{{ $fieldId }}"
                                                        wire:change="saveWorksheet('{{ $crId }}')"
                                                        value="1">
                                                @else
                                                    <input type="text" class="form-control form-control-sm fws-input text-center"
                                                        placeholder="—"
                                                        wire:model.live.debounce.1000ms="worksheetData.{{ $crId }}.mandatory.{{ $fieldId }}"
                                                        wire:blur="saveWorksheet('{{ $crId }}')">
                                                @endif
                                            </td>
                                        @endforeach

                                        <!-- Steps Columns -->
                                        @foreach($tabularSteps as $step)
                                            @php
                                                $stepId = (string) $step->id;
                                                $stepType = $step->step_type;
                                            @endphp
                                            <td class="align-middle">
                                                @if(in_array($stepType, ['input', 'dataset']))
                                                    <input type="text" class="form-control form-control-sm fws-input text-center"
                                                        placeholder="—"
                                                        wire:model.live.debounce.1000ms="worksheetData.{{ $crId }}.steps.{{ $stepId }}"
                                                        wire:blur="saveWorksheet('{{ $crId }}')">
                                                @elseif($stepType === 'checkbox')
                                                    @php
                                                        $cbOptions = $this->checkboxOptionsForStep($step);
                                                        $cbSelected = $worksheetData[$crId]['steps'][$stepId] ?? [];
                                                        if (is_string($cbSelected)) {
                                                            $cbSelected = json_decode($cbSelected, true) ?: [];
                                                        }
                                                    @endphp
                                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                        @foreach($cbOptions as $opt)
                                                            @php
                                                                $optId = (string) $opt->id;
                                                                $isChecked = in_array($optId, $cbSelected, true);
                                                            @endphp
                                                            <div class="form-check form-check-inline m-0">
                                                                <input type="checkbox" class="form-check-input"
                                                                    id="cb-{{ $crId }}-{{ $stepId }}-{{ $optId }}"
                                                                    wire:click="toggleCheckboxForSample('{{ $crId }}', '{{ $stepId }}', '{{ $optId }}')"
                                                                    @if($isChecked) checked @endif>
                                                                <label class="form-check-label small" for="cb-{{ $crId }}-{{ $stepId }}-{{ $optId }}">
                                                                    {{ $opt->label }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @elseif(in_array($stepType, ['derived', 'lookup']))
                                                    @php
                                                        $val = $worksheetData[$crId]['steps'][$stepId] ?? '';
                                                        $hasVal = $val !== '';
                                                    @endphp
                                                    <span class="badge {{ $hasVal ? 'badge-success' : 'badge-light border' }} p-2">
                                                        {{ $val !== '' ? $val : '—' }}
                                                    </span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        @endforeach

                                        <!-- Final Result Column -->
                                        <td class="align-middle font-weight-bold bg-white">
                                            @php
                                                $finalRes = $worksheetData[$crId]['final_result'] ?? '';
                                            @endphp
                                            <span class="badge badge-primary p-2">
                                                {{ $finalRes !== '' ? $finalRes : '—' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            @php
                $mandatoryFieldsTop = $mandatoryFields->filter(fn ($f) => ($f->form_placement ?? 'bottom') === 'top');
                $mandatoryFieldsBottom = $mandatoryFields->filter(fn ($f) => ($f->form_placement ?? 'bottom') !== 'top');
            @endphp

            @if($mandatoryFieldsTop->isNotEmpty())
                @include('livewire.worksheets.partials.formula-mandatory-fields', [
                    'mandatoryFields' => $mandatoryFieldsTop,
                    'title' => 'Mandatory Fields — Top of Form',
                ])
            @endif

            {{-- ── Section 1: Worksheet run metadata ──────────────────────────────── --}}
            <div class="card border-0 shadow-sm mb-4 fws-card">
                <div class="card-header fws-card-header">
                    <i class="mdi mdi-clipboard-text-outline text-primary"></i>
                    <span>Worksheet Run Details</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-2">
                            <label class="fws-label">Date</label>
                            <input type="date" class="form-control form-control-sm"
                                   wire:model="sharedWorksheetMeta.date">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="fws-label">Lab No</label>
                            <input type="text" class="form-control form-control-sm bg-light"
                                   value="{{ $batch->batch_code }}" readonly>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="fws-label">Time In</label>
                            <input type="time" class="form-control form-control-sm"
                                   wire:model="sharedWorksheetMeta.time_in">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="fws-label">Done By</label>
                            <select class="form-control form-control-sm no-select2"
                                    wire:model="sharedWorksheetMeta.done_by_user_id">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="fws-label">Time Out</label>
                            <input type="time" class="form-control form-control-sm"
                                   wire:model="sharedWorksheetMeta.time_out">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="fws-label">Read Date</label>
                            <input type="date" class="form-control form-control-sm"
                                   wire:model="sharedWorksheetMeta.read_date">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="fws-label">Read By</label>
                            <select class="form-control form-control-sm no-select2"
                                    wire:model="sharedWorksheetMeta.read_by_user_id">
                                <option value="">Select...</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Section 2: Formula steps in execution order ───────────────────────── --}}
            @php
                $execSteps = $formulaSteps
                    ->whereIn('step_type', ['input', 'dataset', 'checkbox', 'derived', 'lookup', 'static_text'])
                    ->sortBy('step_number');
                $hasExecSteps = $execSteps->isNotEmpty();
            @endphp
            @if($hasExecSteps)
            <div class="card border-0 shadow-sm mb-4 fws-card">
                <div class="card-header fws-card-header">
                    <i class="mdi mdi-function-variant text-info"></i>
                    <span>Formula Inputs &amp; Calculated Values</span>
                    <span class="fws-all-samples-badge ml-auto">
                        <i class="mdi mdi-account-multiple-outline"></i> Applied to all samples
                    </span>
                </div>
                <div class="card-body px-4 py-4">
                    {{-- Group consecutive steps of the same display category together in rows --}}
                    @php
                        $fieldStepTypes = ['input', 'dataset', 'derived', 'lookup'];
                        $fieldSteps = $execSteps->whereIn('step_type', $fieldStepTypes);
                        $textSteps  = $execSteps->where('step_type', 'static_text');
                        $cbSteps    = $execSteps->where('step_type', 'checkbox');
                    @endphp

                    {{-- Render all steps in strict step_number order --}}
                    @foreach($execSteps as $step)
                        {{-- ─ INPUT / DATASET ─ --}}
                        @if(in_array($step->step_type, ['input', 'dataset']))
                            @if($loop->first || !in_array($execSteps->get($loop->index - 1)?->step_type ?? '', ['input','dataset']))
                            <div class="fws-step-group-label">
                                <i class="mdi mdi-pencil-outline"></i> Inputs
                            </div>
                            <div class="row g-3 mb-4">
                            @endif
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                    <label class="fws-label">
                                        {{ $step->label }}
                                        <span class="fws-type-pill fws-type-pill--input">{{ strtoupper($step->step_type) }}</span>
                                    </label>
                                    <input type="text"
                                           class="form-control form-control-sm fws-input"
                                           wire:model.live="sharedInputStepValues.{{ $step->id }}"
                                           placeholder="Enter value">
                                </div>
                            @if($loop->last || !in_array($execSteps->get($loop->index + 1)?->step_type ?? '', ['input','dataset']))
                            </div>
                            @endif

                        {{-- ─ CHECKBOX ─ --}}
                        @elseif($step->step_type === 'checkbox')
                            @php
                                $cbOptions  = $this->checkboxOptionsForStep($step);
                                $cbSelected = $sharedCheckboxStepData[$step->id] ?? [];
                            @endphp
                            <div class="fws-checkbox-block mb-4" wire:key="cb-{{ $step->id }}">
                                <div class="fws-step-group-label">
                                    <i class="mdi mdi-checkbox-marked-outline"></i> {{ $step->label }}
                                    <span class="fws-type-pill fws-type-pill--checkbox">CHECKBOX</span>
                                </div>
                                @if($step->description)
                                    <p class="text-muted small mb-3">{{ $step->description }}</p>
                                @endif
                                @if($cbOptions->isEmpty())
                                    <p class="text-muted small">No options configured.</p>
                                @else
                                    <div class="fws-checkbox-options">
                                        @foreach($cbOptions as $opt)
                                            @php $optId = (string) $opt->id; $isChecked = in_array($optId, $cbSelected, true); @endphp
                                            <label class="fws-checkbox-option {{ $isChecked ? 'fws-checkbox-option--checked' : '' }}"
                                                   wire:key="cbopt-{{ $optId }}"
                                                   wire:click="toggleSharedCheckboxOption(@js($step->id), @js($optId))">
                                                <span class="fws-checkbox-indicator">
                                                    @if($isChecked)
                                                        <i class="mdi mdi-check-circle text-primary"></i>
                                                    @else
                                                        <i class="mdi mdi-circle-outline text-muted"></i>
                                                    @endif
                                                </span>
                                                <span class="fws-checkbox-text">{{ $opt->label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                        {{-- ─ STATIC TEXT ─ --}}
                        @elseif($step->step_type === 'static_text')
                            @php $txtContent = $step->staticTextContent(); @endphp
                            @if(trim($txtContent) !== '')
                            <div class="fws-static-text-block mb-4" wire:key="st-{{ $step->id }}">
                                <div class="fws-step-group-label">
                                    <i class="mdi mdi-text-box-outline"></i> {{ $step->label }}
                                </div>
                                <div class="fws-static-text-body">{{ $txtContent }}</div>
                            </div>
                            @endif

                        {{-- ─ DERIVED / LOOKUP ─ --}}
                        @elseif(in_array($step->step_type, ['derived', 'lookup']))
                            @if($loop->first || !in_array($execSteps->get($loop->index - 1)?->step_type ?? '', ['derived','lookup']))
                            @php
                                $derivedGroupCount = 0;
                                for ($derivedIdx = $loop->index; $derivedIdx < $execSteps->count(); $derivedIdx++) {
                                    $derivedStep = $execSteps->values()[$derivedIdx] ?? null;
                                    if (! $derivedStep || ! in_array($derivedStep->step_type, ['derived', 'lookup'], true)) {
                                        break;
                                    }
                                    $derivedGroupCount++;
                                }
                                $derivedGridClass = match (true) {
                                    $derivedGroupCount === 1 => 'fws-calc-fields-row--one',
                                    $derivedGroupCount === 2 => 'fws-calc-fields-row--two',
                                    default => 'fws-calc-fields-row--three',
                                };
                            @endphp
                            <div class="fws-step-group-label">
                                <i class="mdi mdi-calculator-variant-outline"></i> Calculated Values
                            </div>
                            <div class="fws-calc-fields-row {{ $derivedGridClass }} mb-4">
                            @endif
                                <div class="fws-calc-field-item">
                                    <div class="fws-calc-field-wrap">
                                    <label class="fws-label fws-label--calc">
                                        {{ $step->label }}
                                        <span class="fws-type-pill fws-type-pill--derived">{{ strtoupper($step->step_type) }}</span>
                                    </label>
                                    @php $derivedVal = $sharedDerivedStepValues[$step->id] ?? ''; @endphp
                                    @if($step->step_type === 'lookup')
                                    <div class="d-flex gap-1 fws-calc-field-body">
                                        <div class="fws-calc-field flex-grow-1 {{ $derivedVal !== '' ? 'fws-calc-field--has-value' : '' }}">
                                            {{ $derivedVal !== '' ? $derivedVal : '—' }}
                                        </div>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary fws-lookup-btn"
                                                wire:click="openChangeLookupModal('{{ $capturedResults->first()?->id }}', '{{ $step->id }}')"
                                                title="Change Lookup Table">
                                            <i class="mdi mdi-swap-horizontal"></i>
                                        </button>
                                    </div>
                                    @else
                                    <div class="fws-calc-field fws-calc-field-body {{ $derivedVal !== '' ? 'fws-calc-field--has-value' : '' }}">
                                        {{ $derivedVal !== '' ? $derivedVal : '—' }}
                                    </div>
                                    @endif
                                    </div>
                                </div>
                            @if($loop->last || !in_array($execSteps->get($loop->index + 1)?->step_type ?? '', ['derived','lookup']))
                            </div>
                            @endif
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

        {{-- ── Section 3: Save button ───────────────────────────────────────────── --}}
        @if(! $worksheetsReadOnly)
        <div class="d-flex justify-content-end mb-4">
            <button type="button" class="btn fws-save-btn"
                    wire:click="saveWorksheetLevel"
                    wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="saveWorksheetLevel">
                    <i class="mdi mdi-content-save-outline"></i> Save Worksheet
                </span>
                <span wire:loading wire:target="saveWorksheetLevel">
                    <i class="mdi mdi-loading mdi-spin"></i> Saving…
                </span>
            </button>
        </div>
        @endif

        @foreach($formulaSteps->where('step_type', 'custom_table')->sortBy('step_number') as $step)
            @include('livewire.worksheets.partials.formula-step-custom-table', ['step' => $step])
        @endforeach

        @include('livewire.worksheets.partials.formula-pcr-plate-map-step')

        @if($mandatoryFieldsBottom->isNotEmpty())
            @include('livewire.worksheets.partials.formula-mandatory-fields', [
                'mandatoryFields' => $mandatoryFieldsBottom,
                'title' => 'Mandatory Fields — Bottom of Form',
            ])
        @endif
        @endif {{-- viewMode table/form --}}
    @else
        <div class="alert alert-info">
            <i class="mdi mdi-information"></i>
            @if($groupedWorksheetHolderId)
                No captured results found for this grouped pipeline in batch {{ $batch->batch_code }}.
            @else
                No captured results found for formula "{{ $formula->name }}" in this batch.
            @endif
        </div>
    @endif

    <!-- Lookup Table Change Modal -->
    @if($showLookupTableModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-secondary text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-swap-horizontal"></i> Change Lookup Table
                            @if($selectedLookupStepId)
                                @php
                                    $selectedStep = $formulaSteps->firstWhere('id', $selectedLookupStepId);
                                @endphp
                                @if($selectedStep)
                                    - {{ $selectedStep->label }}
                                @endif
                            @endif
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeLookupModal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Warning -->
                        <div class="alert alert-warning">
                            <i class="mdi mdi-alert"></i>
                            <strong>Note:</strong> This change only affects this worksheet. The formula configuration will not be changed.
                        </div>

                        @if(count($compatibleLookupTables) === 0 && $currentLookupTableInfo)
                            <div class="alert alert-info">
                                <div class="mb-2">
                                    <strong>Current table:</strong> {{ $currentLookupTableInfo['name'] }}
                                </div>
                                <div class="mb-2">
                                    <strong>Compatibility rules:</strong>
                                    same lookup type and same key columns.
                                </div>
                                <div>
                                    <strong>Required key columns:</strong>
                                    <code>{{ implode(', ', $currentLookupTableInfo['key_columns'] ?? []) }}</code>
                                </div>
                            </div>
                        @endif

                        <!-- Original Lookup Table -->
                        @if($selectedLookupStepId)
                            @php
                                $originalLookup = $this->getOriginalLookupTable($selectedLookupStepId);
                            @endphp
                            @if($originalLookup)
                                <div class="mb-3">
                                    <label class="font-weight-bold">Formula Default:</label>
                                    <div class="alert alert-light border">
                                        {{ $originalLookup->name }}
                                        <small class="d-block text-muted">{{ $originalLookup->description }}</small>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <!-- Compatible Lookup Tables Selection -->
                        <div class="mb-3">
                            <label class="font-weight-bold">Select Replacement Lookup Table:</label>
                            @if(count($compatibleLookupTables) > 0)
                                <div class="list-group">
                                    @foreach($compatibleLookupTables as $lookupTable)
                                        <label class="list-group-item list-group-item-action cursor-pointer">
                                            <div class="d-flex align-items-center">
                                                <input type="radio" 
                                                       name="replacement_lookup_table" 
                                                       wire:model="selectedReplacementLookupTableId" 
                                                       value="{{ $lookupTable['id'] }}"
                                                       class="mr-2">
                                                <div class="flex-grow-1">
                                                    <strong>{{ $lookupTable['name'] }}</strong>
                                                    @if($lookupTable['description'])
                                                        <small class="d-block text-muted">{{ $lookupTable['description'] }}</small>
                                                    @endif
                                                    <small class="badge badge-info">
                                                        {{ $lookupTable['lookup_type'] === 'range_based' ? 'Range-Based' : 'Key-Value' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="mdi mdi-information"></i>
                                    No compatible lookup tables found.
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="closeLookupModal">
                            <i class="mdi mdi-close"></i> Cancel
                        </button>
                        @if($currentCapturedResultId && $selectedLookupStepId && isset($worksheetData[$currentCapturedResultId]['lookup_overrides'][$selectedLookupStepId]))
                            <button type="button" class="btn btn-warning btn-sm" wire:click="resetLookupTable">
                                <i class="mdi mdi-refresh"></i> Reset to Default
                            </button>
                        @endif
                        <button type="button" 
                                class="btn btn-primary btn-sm" 
                                wire:click="changeLookupTable"
                                @if(count($compatibleLookupTables) == 0) disabled @endif>
                            <i class="mdi mdi-check"></i> Apply
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Post Results Confirmation Modal -->
    @if($showPostResultsModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">
                    @include('worksheets.partials.post-results-modal-shell', [
                        'modalTitle' => 'Confirm Standards & Post Results',
                        'postingInProgress' => $postingInProgress,
                        'hideFooter' => true,
                    ])
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        @if(!$postingInProgress)
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Start Analysis Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" wire:model="startAnalysisDate">
                                    @error('startAnalysisDate') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                    <small class="text-muted">Date when analysis started</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">End Analysis Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" wire:model="endAnalysisDate">
                                    @error('endAnalysisDate') <span class="text-danger d-block">{{ $message }}</span> @enderror
                                    <small class="text-muted">Date when analysis ended</small>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="mdi mdi-information"></i>
                                <strong>Confirm or update standards</strong> for each result before posting.
                                Changes update the shared sample standards and affect future results.
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Sample Code</th>
                                            <th>Analyte</th>
                                            <th>Result</th>
                                            <th style="min-width: 200px;">Main Standard</th>
                                            <th style="min-width: 200px;">Secondary Standard</th>
                                            @if($lookupStandardInfo)
                                                <th>Use Lookup</th>
                                            @endif
                                            <th>Standard Limits</th>
                                            <th>Remark</th>
                                            <th>Method</th>
                                            <th>Reporting Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($postResultsPreviewRows as $row)
                                            @php
                                                $sampleId = (string) ($row['sample_id'] ?? '');
                                                $remark = strtoupper(trim((string) ($row['remark'] ?? '')));
                                                $isPass = $remark === 'PASS' || $remark === 'COMPLIANT';
                                                $isFail = $remark === 'FAIL' || $remark === 'NON-COMPLIANT';
                                            @endphp
                                            <tr wire:key="post-preview-{{ $row['captured_result_id'] }}">
                                                <td><strong>{{ $row['sample_code'] }}</strong></td>
                                                <td>{{ $row['analyte'] ?: '—' }}</td>
                                                <td>
                                                    @if(!empty($row['reporting_symbol']))
                                                        {{ $row['reporting_symbol'] }}
                                                    @endif
                                                    {{ $row['result'] !== '' ? $row['result'] : '—' }}
                                                </td>
                                                <td>
                                                    <div class="tag-select-container" wire:click="toggleMainStandardDropdown(@js($sampleId))">
                                                        <div class="tag-select-input">
                                                            @if(!empty($row['main_standard']))
                                                                <span class="tag-badge">
                                                                    {{ $this->getSelectedStandardName($row['main_standard']) }}
                                                                    <i class="mdi mdi-close-circle"
                                                                       wire:click.stop="updateSampleStandard(@js($sampleId), 'main', null)"></i>
                                                                </span>
                                                            @endif
                                                            <input type="text"
                                                                   wire:model.live="mainStandardSearch.{{ $sampleId }}"
                                                                   wire:keyup="searchMainStandards(@js($sampleId))"
                                                                   class="tag-input"
                                                                   placeholder="{{ !empty($row['main_standard']) ? '' : 'Not Set' }}"
                                                                   autocomplete="off">
                                                        </div>
                                                        @if(!empty($showMainStandardDropdown[$sampleId]))
                                                            <div class="tag-dropdown" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: white; border: 1px solid #ddd; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); min-width: 280px; max-width: 400px;">
                                                                <div style="max-height: 200px; overflow-y: auto;">
                                                                    <div class="tag-dropdown-item"
                                                                         wire:click.stop="updateSampleStandard(@js($sampleId), 'main', null)">
                                                                        <span class="text-muted">Not Set</span>
                                                                    </div>
                                                                    @foreach($filteredMainStandards[$sampleId] ?? $availableStandards as $standard)
                                                                        <div class="tag-dropdown-item"
                                                                             wire:click.stop="updateSampleStandard(@js($sampleId), 'main', @js($standard->id))">
                                                                            {{ $standard->name }} ({{ $standard->code }})
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="tag-select-container" wire:click="toggleSecondaryStandardDropdown(@js($sampleId))">
                                                        <div class="tag-select-input">
                                                            @if(!empty($row['secondary_standard']))
                                                                <span class="tag-badge">
                                                                    {{ $this->getSelectedStandardName($row['secondary_standard']) }}
                                                                    <i class="mdi mdi-close-circle"
                                                                       wire:click.stop="updateSampleStandard(@js($sampleId), 'secondary', null)"></i>
                                                                </span>
                                                            @endif
                                                            <input type="text"
                                                                   wire:model.live="secondaryStandardSearch.{{ $sampleId }}"
                                                                   wire:keyup="searchSecondaryStandards(@js($sampleId))"
                                                                   class="tag-input"
                                                                   placeholder="{{ !empty($row['secondary_standard']) ? '' : 'Not Set' }}"
                                                                   autocomplete="off">
                                                        </div>
                                                        @if(!empty($showSecondaryStandardDropdown[$sampleId]))
                                                            <div class="tag-dropdown" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: white; border: 1px solid #ddd; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); min-width: 280px; max-width: 400px;">
                                                                <div style="max-height: 200px; overflow-y: auto;">
                                                                    <div class="tag-dropdown-item"
                                                                         wire:click.stop="updateSampleStandard(@js($sampleId), 'secondary', null)">
                                                                        <span class="text-muted">Not Set</span>
                                                                    </div>
                                                                    @foreach($filteredSecondaryStandards[$sampleId] ?? $availableStandards as $standard)
                                                                        <div class="tag-dropdown-item"
                                                                             wire:click.stop="updateSampleStandard(@js($sampleId), 'secondary', @js($standard->id))">
                                                                            {{ $standard->name }} ({{ $standard->code }})
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                @if($lookupStandardInfo)
                                                    <td class="text-center">
                                                        <div class="form-check d-inline-block">
                                                            <input type="checkbox"
                                                                   class="form-check-input"
                                                                   wire:model.live="useLookupAsStandard.{{ $sampleId }}"
                                                                   id="use-lookup-{{ $row['captured_result_id'] }}">
                                                            <label class="form-check-label" for="use-lookup-{{ $row['captured_result_id'] }}">
                                                                <small class="text-muted d-block">{{ $lookupStandardInfo['name'] ?? 'Lookup' }}</small>
                                                            </label>
                                                        </div>
                                                    </td>
                                                @endif
                                                <td>{{ $row['standard_limits'] ?: '—' }}</td>
                                                <td>
                                                    @if($isPass)
                                                        <span class="badge badge-success">{{ $remark }}</span>
                                                    @elseif($isFail)
                                                        <span class="badge badge-danger">{{ $remark }}</span>
                                                    @elseif($remark !== '' && $remark !== '-')
                                                        <span class="badge badge-secondary">{{ $row['remark'] }}</span>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td>{{ $row['method'] ?: '—' }}</td>
                                                <td>{{ $row['reporting_unit'] ?: '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="mdi mdi-alert"></i>
                                <strong>Note:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Standard changes update the shared sample record and affect future results</li>
                                    @if($lookupStandardInfo)
                                        <li>When "Use Lookup" is checked, remark comes from the lookup table instead of standard-based PASS/FAIL</li>
                                    @endif
                                    <li>Posting will use the standards and remark mode shown above</li>
                                </ul>
                            </div>
                        @else
                            <!-- Progress Tracking View -->
                            <div class="text-center py-4">
                                <h5 class="mb-4">
                                    <i class="mdi mdi-loading mdi-spin text-primary"></i> 
                                    Posting Results...
                                </h5>
                                
                                <!-- Progress Bar -->
                                <div class="mb-4">
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                             role="progressbar" 
                                             style="width: {{ ($currentStep / $totalSteps) * 100 }}%"
                                             aria-valuenow="{{ $currentStep }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="{{ $totalSteps }}">
                                            Step {{ $currentStep }} of {{ $totalSteps }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Current Step Message -->
                                <div class="alert alert-light border">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <i class="mdi mdi-progress-clock mdi-24px text-primary mr-2"></i>
                                        <div>
                                            <strong>{{ $currentStepMessage }}</strong><br>
                                            @if($currentStep > 1)
                                                <small class="text-muted">
                                                    Processing {{ $processedCount }} of {{ $totalCount }} results...
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Step Indicators -->
                                <div class="row mt-4">
                                    <div class="col-md-4">
                                        <div class="card {{ $currentStep >= 1 ? 'border-success' : 'border-secondary' }}">
                                            <div class="card-body text-center py-3">
                                                <i class="mdi mdi-{{ $currentStep > 1 ? 'check-circle text-success' : ($currentStep == 1 ? 'loading mdi-spin text-primary' : 'circle-outline text-secondary') }} mdi-36px"></i>
                                                <p class="mb-0 mt-2"><small>Step 1: Confirm Standards</small></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card {{ $currentStep >= 2 ? 'border-success' : 'border-secondary' }}">
                                            <div class="card-body text-center py-3">
                                                <i class="mdi mdi-{{ $currentStep > 2 ? 'check-circle text-success' : ($currentStep == 2 ? 'loading mdi-spin text-primary' : 'circle-outline text-secondary') }} mdi-36px"></i>
                                                <p class="mb-0 mt-2"><small>Step 2: Post Results</small></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card {{ $currentStep >= 3 ? 'border-success' : 'border-secondary' }}">
                                            <div class="card-body text-center py-3">
                                                <i class="mdi mdi-{{ $currentStep > 3 ? 'check-circle text-success' : ($currentStep == 3 ? 'loading mdi-spin text-primary' : 'circle-outline text-secondary') }} mdi-36px"></i>
                                                <p class="mb-0 mt-2"><small>Step 3: Calculate Remarks</small></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    @include('worksheets.partials.post-results-modal-shell', [
                        'modalTitle' => 'Confirm Standards & Post Results',
                        'postingInProgress' => $postingInProgress,
                        'hideHeader' => true,
                        'hideFooter' => false,
                    ])
                </div>
            </div>
        </div>
    @endif

    <style>
    /* Tag-based Dropdown Styling */
    .tag-select-container {
        position: relative;
        cursor: text;
    }
    
    .tag-select-input {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        min-height: 38px;
        padding: 6px 12px;
        background: #fff;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .tag-select-input:hover {
        border-color: #007bff;
    }
    
    .tag-select-input:focus-within {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        outline: none;
    }
    
    .tag-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        background-color: #007bff;
        color: white;
        border-radius: 16px;
        font-size: 0.875rem;
        font-weight: 500;
        white-space: nowrap;
    }
    
    .tag-badge i {
        cursor: pointer;
        font-size: 1rem;
        opacity: 0.8;
        transition: opacity 0.2s;
    }
    
    .tag-badge i:hover {
        opacity: 1;
    }
    
    .tag-input {
        flex: 1;
        min-width: 80px;
        border: none;
        outline: none;
        padding: 4px;
        font-size: 0.875rem;
    }
    
    .tag-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 2px solid #007bff;
        border-top: none;
        border-radius: 0 0 8px 8px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1050;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-top: -2px;
    }
    
    .tag-dropdown-item {
        padding: 8px 12px;
        cursor: pointer;
        transition: background-color 0.2s;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .tag-dropdown-item:hover {
        background-color: #f8f9fa;
    }
    
    .tag-dropdown-item:last-child {
        border-bottom: none;
    }
    </style>
    
    <script>
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.tag-select-container')) {
            Livewire.dispatch('closeAllDropdowns');
        }
    });

    // Simple dropdown positioning
    document.addEventListener('livewire:init', () => {
        Livewire.on('dropdownOpened', function(data) {
            // Just ensure dropdowns are visible
            setTimeout(() => {
                const dropdowns = document.querySelectorAll('.tag-dropdown');
                dropdowns.forEach(dropdown => {
                    dropdown.style.display = 'block';
                });
            }, 10);
        });
    });
    </script>

    <style>
        /* ── Cards ─────────────────────────────────────────────────── */
        .fws-card { border-radius: 14px; overflow: hidden; }
        .fws-card-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.85rem 1.5rem;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid #e2e8f0;
            font-weight: 700;
            font-size: 0.875rem;
            color: #1e293b;
        }
        .fws-all-samples-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.7rem;
            font-weight: 600;
            color: #64748b;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 2px 10px;
        }

        /* ── Labels ─────────────────────────────────────────────────── */
        .fws-label {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 0.35rem;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #64748b;
        }
        .fws-label--calc {
            align-items: flex-start;
            flex-wrap: wrap;
            margin-bottom: 0.5rem;
        }

        /* ── Calculated value grid (max 3 per row, 50/50 when 2 items) ─ */
        .fws-calc-fields-row {
            display: grid;
            gap: 1rem;
            align-items: stretch;
        }
        .fws-calc-fields-row--one {
            grid-template-columns: 1fr;
        }
        .fws-calc-fields-row--two {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .fws-calc-fields-row--three {
            grid-template-columns: repeat(6, minmax(0, 1fr));
        }
        .fws-calc-fields-row--three .fws-calc-field-item {
            grid-column: span 2;
        }
        /* Last row with exactly 2 items → half width each */
        .fws-calc-fields-row--three .fws-calc-field-item:nth-last-child(2):nth-child(3n+1),
        .fws-calc-fields-row--three .fws-calc-field-item:last-child:nth-child(3n+2) {
            grid-column: span 3;
        }
        @media (max-width: 767.98px) {
            .fws-calc-fields-row--two,
            .fws-calc-fields-row--three {
                grid-template-columns: 1fr;
            }
        }
        @media (min-width: 768px) and (max-width: 991.98px) {
            .fws-calc-fields-row--three {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .fws-calc-fields-row--three .fws-calc-field-item {
                grid-column: span 1;
            }
        }
        .fws-calc-field-item {
            min-width: 0;
        }
        .fws-calc-field-wrap {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .fws-calc-field-body {
            flex: 1 1 auto;
            display: flex;
            align-items: flex-start;
            min-height: 3.25rem;
        }
        .fws-calc-field-body.fws-calc-field,
        .fws-calc-field-body .fws-calc-field {
            width: 100%;
            height: 100%;
        }

        /* ── Step group heading ──────────────────────────────────────── */
        .fws-step-group-label {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #94a3b8;
            margin-bottom: 0.85rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px dashed #e2e8f0;
        }

        /* ── Type pills ─────────────────────────────────────────────── */
        .fws-type-pill {
            font-size: 0.58rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            padding: 1px 6px;
            border-radius: 20px;
            vertical-align: middle;
        }
        .fws-type-pill--input    { background: #dbeafe; color: #1d4ed8; }
        .fws-type-pill--derived  { background: #dcfce7; color: #15803d; }
        .fws-type-pill--checkbox { background: #fce7f3; color: #9d174d; }

        /* ── Input fields ───────────────────────────────────────────── */
        .fws-input {
            border-color: #cbd5e1;
            border-radius: 8px;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .fws-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
        }

        /* ── Calculated value display ────────────────────────────────── */
        .fws-calc-field {
            min-height: 31px;
            padding: 0.35rem 0.65rem;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 0.8125rem;
            color: #94a3b8;
            font-style: italic;
        }
        .fws-calc-field--has-value {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #15803d;
            font-style: normal;
            font-weight: 600;
        }
        .fws-lookup-btn { border-radius: 8px; padding: 0.25rem 0.5rem; }

        /* ── Checkbox options ────────────────────────────────────────── */
        .fws-checkbox-block { padding: 1rem 1.25rem; background: #fafbff; border-radius: 10px; border: 1px solid #e8eaf0; }
        .fws-checkbox-options { display: flex; flex-wrap: wrap; gap: 0.75rem; }
        .fws-checkbox-option {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1rem;
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            background: #fff;
            cursor: pointer;
            font-size: 0.85rem;
            color: #374151;
            font-weight: 500;
            transition: all 0.15s ease;
            user-select: none;
        }
        .fws-checkbox-option:hover { border-color: #93c5fd; background: #eff6ff; }
        .fws-checkbox-option--checked { border-color: #3b82f6; background: #eff6ff; color: #1d4ed8; font-weight: 600; }
        .fws-checkbox-indicator { font-size: 1.1rem; line-height: 1; }

        /* ── Static text ─────────────────────────────────────────────── */
        .fws-static-text-block { padding: 1rem 1.25rem; background: #fffbeb; border-radius: 10px; border: 1px solid #fde68a; }
        .fws-static-text-body { font-size: 0.85rem; color: #78350f; white-space: pre-wrap; line-height: 1.6; margin-top: 0.5rem; }

        /* ── Save button ─────────────────────────────────────────────── */
        .fws-save-btn {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 0.5rem 1.75rem;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.875rem;
            box-shadow: 0 1px 3px rgba(37,99,235,0.3);
            transition: background 0.15s ease;
        }
        .fws-save-btn:hover { background: #1d4ed8; color: #fff; }
        .fws-save-btn:disabled { opacity: 0.65; }
    </style>
</div>
