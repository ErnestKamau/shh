<div class="method-section">
    <div class="info-row">
        <div class="info-label" style="width: 15%;">Test Section:</div>
        <div class="info-value">{{ $testSectionName ?? 'N/A' }}</div>
    </div>

    <div class="info-row">
        <div class="info-label" style="width: 15%;">Test Required:</div>
        <div class="info-value">
            {{ $testsRequired ?? ($batch->description ?? 'Unknown Analysis') }}
        </div>
    </div>

    <div class="info-row">
        <div class="info-label" style="width: 15%;">Method Used:</div>
        <div class="info-value">
            @php
            $methods = [];
            foreach($parameters ?? [] as $parameter) {
                $method = $parameter->method_name ?? $parameter->method_code ?? null;
                if ($method) {
                    $methods[] = $method;
                }
            }
            $uniqueMethods = array_unique($methods);
            $methodText = !empty($uniqueMethods) ? implode(', ', $uniqueMethods) : 'N/A';
            @endphp
            {{ $methodText }}
        </div>
    </div>

    <div class="info-row">
        <div class="info-label" style="width: 15%;">Deviations from method?</div>
        <div class="info-value">
            {{ ($batch->has_method_deviation ?? false) ? 'Yes' : 'No' }}
        </div>
    </div>

    <div class="info-row">
        <div class="info-label" style="width: 15%;">Reason for Deviation:</div>
        <div class="info-value">
            @if($batch->has_method_deviation ?? false)
                {{ $batch->method_deviation_reason ?: 'N/A' }}
            @else
                N/A
            @endif
        </div>
    </div>
</div>