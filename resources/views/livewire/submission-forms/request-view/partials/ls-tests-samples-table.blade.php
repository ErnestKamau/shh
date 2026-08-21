{{-- Reusable tests/samples table (Tests tab + TRF view modal). Props: $columns, $samples, $showEditActions --}}
@php
    $columns = $columns ?? [];
    $samples = $samples ?? [];
    $showEditActions = $showEditActions ?? false;
@endphp
<div class="ls-table-wrap rv-tests-table-wrap">
    <table class="ls-table rv-tests-table mb-0">
        <thead>
            <tr>
                @foreach($columns as $column)
                    @php $isActions = ($column['key'] ?? '') === 'actions'; @endphp
                    <th @class(['text-center' => $isActions]) @if($isActions) style="width: 88px;" @endif>
                        {{ $column['label'] }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($samples as $sample)
                <tr wire:key="sample-row-{{ $sample['row_index'] ?? $sample['number'] }}">
                    @foreach($columns as $column)
                        @php $columnKey = $column['key'] ?? ''; @endphp
                        @if($columnKey === 'actions')
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center" style="gap: 4px;">
                                    <button type="button"
                                        class="btn btn-sm btn-icon btn-light text-info"
                                        title="View parameters"
                                        aria-label="View parameters for sample {{ $sample['number'] }}"
                                        @click='modalTitle = "Parameters"; parameterGroups = @json($sample["parameter_groups"] ?? []); paramsOpen = true;'>
                                        <i class="mdi mdi-flask-outline" aria-hidden="true"></i>
                                    </button>
                                    @if($showEditActions)
                                        <button type="button"
                                            class="btn btn-sm btn-icon btn-light text-primary"
                                            title="Edit sample row"
                                            aria-label="Edit sample row {{ $sample['number'] }}"
                                            wire:click="openSampleRowEditor({{ (int) ($sample['row_index'] ?? 0) }})">
                                            <i class="mdi mdi-pencil" aria-hidden="true"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        @elseif($columnKey === 'sample_description')
                            <td class="rv-sample-description-cell">
                                @if(! empty($sample['has_description']))
                                    <div class="rv-sample-description-inline">{!! $sample['sample_description_html'] !!}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        @elseif($columnKey === 'test_requirements')
                            <td>{{ $sample['test_category'] ?? '—' }}</td>
                        @elseif(str_starts_with($columnKey, 'extra:'))
                            @php
                                $extraLabel = $column['label'] ?? '';
                                $extraValue = collect($sample['extra_columns'] ?? [])->firstWhere('label', $extraLabel)['value'] ?? '—';
                            @endphp
                            <td>{{ $extraValue }}</td>
                        @else
                            <td>{{ $sample[$columnKey] ?? '—' }}</td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
