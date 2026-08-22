@php
    $dossier = $dossier ?? [];
    $displayLabel = (string) ($dossier['display_label'] ?? 'Sample');
    $identity = is_array($dossier['identity'] ?? null) ? $dossier['identity'] : [];
    $tests = is_array($dossier['tests'] ?? null) ? $dossier['tests'] : [];
@endphp
<section class="integrity-dossier-panel ls-trf-sample-panel is-expanded" aria-label="Sample dossier">
    <div class="integrity-dossier-header ls-soft-card__header ls-trf-sample-panel__header">
        <span class="ls-trf-sample-panel__title">{{ $displayLabel }}</span>
    </div>
    <div class="integrity-dossier-body ls-soft-card__body ls-trf-sample-panel__body">
        @if(! empty($dossier['condition_not_acceptable']))
            <div class="integrity-sample-condition-flag mb-2" title="Sample condition recorded at receive">
                <i class="mdi mdi-flag" aria-hidden="true"></i>
                Condition: Not Acceptable
                @if(! empty($dossier['condition_name']))
                    ({{ $dossier['condition_name'] }})
                @endif
            </div>
        @elseif(! empty($dossier['condition_name']))
            <div class="integrity-dossier-meta mb-2">
                <span class="integrity-dossier-meta__label">Condition</span>
                <span>{{ $dossier['condition_name'] }}</span>
            </div>
        @endif

        @if($identity !== [])
            <div class="integrity-dossier-section">
                <h3 class="integrity-dossier-section__title ls-type-label">Sample details</h3>
                <dl class="integrity-dossier-fields">
                    @foreach($identity as $field)
                        <div class="integrity-dossier-field">
                            <dt>{{ $field['label'] ?? '' }}</dt>
                            <dd>{{ $field['value'] ?? '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif

        <div class="integrity-dossier-section">
            <h3 class="integrity-dossier-section__title ls-type-label">Tests</h3>
            @if($tests === [])
                <p class="text-muted small mb-0">No tests configured for this sample.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0 integrity-dossier-tests-table">
                        <thead>
                            <tr>
                                <th>Test</th>
                                <th>TAT</th>
                                <th>Method</th>
                                <th>Sample type</th>
                                <th>Analysis type</th>
                                <th>Test category</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tests as $testRow)
                                <tr class="{{ ! empty($testRow['subcontracted']) ? 'is-subcontracted' : '' }}">
                                    <td>
                                        <span class="font-weight-bold">{{ $testRow['test'] ?? '—' }}</span>
                                        @if(! empty($testRow['subcontracted']))
                                            <span class="badge badge-light ml-1">Subcontracted</span>
                                        @endif
                                    </td>
                                    <td>{{ filled($testRow['tat'] ?? null) ? $testRow['tat'] : '—' }}</td>
                                    <td>{{ filled($testRow['method'] ?? null) ? $testRow['method'] : '—' }}</td>
                                    <td>{{ filled($testRow['sample_type'] ?? null) ? $testRow['sample_type'] : '—' }}</td>
                                    <td>{{ filled($testRow['analysis_type'] ?? null) ? $testRow['analysis_type'] : '—' }}</td>
                                    <td>{{ filled($testRow['test_category'] ?? null) ? $testRow['test_category'] : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</section>
