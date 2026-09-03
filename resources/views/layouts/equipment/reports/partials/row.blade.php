@php
    $isDisposed = (bool) ($row->is_disposal ?? false);
    $assetTypeLabel = method_exists($row, 'assetTypeLabel')
        ? $row->assetTypeLabel()
        : (getAssetTypeById($row->asset_type_id)?->descripton
            ?? (trim((string) ($row->asset_description ?? $row->asset_code ?? '')) ?: '-'));
    $assetLocationLabel = $row->relationLoaded('assetLocation') && $row->assetLocation
        ? $row->assetLocation->name
        : (getAssetLocationByid($row->asset_location_id)?->name ?? '-');
    $departmentLabel = getInventoryDepartmentName($row->assigned_department)
        ?? ($row->relationLoaded('department') && $row->department ? $row->department->name : null)
        ?? '-';
@endphp

<tr>
    @if ($reportName === 'equipment_report')
        <td>{{ $row->name }}</td>
        <td>
            <span class="eq-report-status {{ $isDisposed ? 'is-disposed' : 'is-active' }}">
                <i class="mdi {{ $isDisposed ? 'mdi-close-circle' : 'mdi-marker-check' }}"></i>
                {{ $isDisposed ? 'Disposed' : 'Active' }}
            </span>
        </td>
        <td>{{ $assetTypeLabel }}</td>
        <td>{{ $assetLocationLabel }}</td>
        <td>{{ $departmentLabel }}</td>
        @php
            $maintainance = $row->maintainance_date();
            $calibration = $row->calibration_date();
        @endphp
        <td class="eq-report-due">
            {{ $maintainance['date']->toDateString() }}
            <span class="badge {{ $maintainance['status'] }} ml-1">
                {{ number_format((int) $maintainance['remaining_days']) }} days
            </span>
            @if ($maintainance['status'] === 'badge-warning')
                <div class="text-warning mt-1"><small>Schedule maintainance</small></div>
            @elseif ($maintainance['status'] === 'badge-danger')
                <div class="text-danger mt-1"><small>Maintainance required</small></div>
            @endif
        </td>
        <td class="eq-report-due">
            {{ $calibration['date']->toDateString() }}
            <span class="badge {{ $calibration['status'] }} ml-1">
                {{ number_format((int) $calibration['remaining_days']) }} days
            </span>
            @if ($calibration['status'] === 'badge-warning')
                <div class="text-warning mt-1"><small>Schedule calibration</small></div>
            @elseif ($calibration['status'] === 'badge-danger')
                <div class="text-danger mt-1"><small>Calibration required</small></div>
            @endif
        </td>
    @endif

    @if ($reportName === 'maintainance_report')
        <td>{{ $row->name }}</td>
        <td>{{ $row->description ?? $row->procedure ?? '-' }}</td>
        <td>{{ isset($row->type) ? $row->type : 'Verification' }} Log</td>
        <td>{{ $row->maintainance_type ?? '-' }}</td>
        <td>{{ $row->correction_factor ?? '-' }}</td>
        <td>{{ $row->uncertainty_of_measure ?? '-' }}</td>
        <td>
            @if (($row->maintainance_type ?? null) === 'in-house')
                {{ getUserById($row->employee_id)?->name ?? '-' }}
            @else
                {{ getSupplierByID($row->supplier_id)?->name ?? '-' }}
            @endif
        </td>
        <td>{{ $assetTypeLabel }}</td>
        <td>{{ $assetLocationLabel }}</td>
        <td>{{ $departmentLabel }}</td>
        <td>{{ $row->notes ?? $row->remark ?? '-' }}</td>
    @endif
</tr>
