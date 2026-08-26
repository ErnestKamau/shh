@php
    $grid = $grid ?? [];
    $apparatus = $grid['apparatus'] ?? [];
    $thermometerId = (string) ($grid['thermometer_id'] ?? '');
    $phMeterId = (string) ($grid['ph_meter_id'] ?? '');
    $chlorineMeterId = (string) ($grid['chlorine_meter_id'] ?? '');
    $apparatusOthers = (string) ($grid['sampling_apparatus_others'] ?? '');
    $extraEquipment = (string) ($grid['extra_sampling_equipment'] ?? '');
    $othersChecked = (bool) ($apparatus['OTHERS'] ?? false);

    $instrumentLine = static function (string $label, string $value, ?bool $forceChecked = null): string {
        $checked = $forceChecked ?? ($value !== '');
        $class = $checked ? 'trf-check trf-check-on' : 'trf-check trf-check-off';
        $valueHtml = $value !== ''
            ? e($value)
            : '<span class="trf-dotted-leader">................</span>';

        return '<span class="'.$class.'"></span> '.$label.' '.$valueHtml;
    };

    $fullWidthRows = [
        $instrumentLine('THERMOMETER ID', $thermometerId),
        $instrumentLine('pH METER', $phMeterId),
        $instrumentLine('CHLORINE METER ID', $chlorineMeterId),
        $instrumentLine('OTHERS', $apparatusOthers, $othersChecked || $apparatusOthers !== ''),
    ];
    if ($extraEquipment !== '') {
        $fullWidthRows[] = $instrumentLine('ADDITIONAL EQUIPMENT', $extraEquipment, true);
    }
@endphp
@include('workflow.forms.test-request.partials.checkbox-grid', [
    'checks' => $apparatus,
    'orderedKeys' => ['STERILE BOTTLE', 'BOTTLE CATCHER'],
    'columns' => 2,
    'bordered' => true,
    'fullWidthRows' => $fullWidthRows,
])
