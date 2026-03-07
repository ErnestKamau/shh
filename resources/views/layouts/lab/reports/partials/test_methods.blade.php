<div class="method-section">
    <div class="section-title">Test Method:</div>
    <div class="three-column">
        <div class="column">
            <div class="info-row">
                <div class="info-label">Test Required:</div>
                <div class="info-value">{{ $batch->description ?? 'Unknown Analysis' }}</div>
            </div>
        </div>
        <div class="column">
            <div class="info-row">
                <div class="info-label">Method Used:</div>
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
        </div>
        <div class="column">
            <div class="info-row">
                <div class="info-label">Deviations from method?</div>
                <div class="info-value">No</div>
            </div>
        </div>
    </div>
    <div class="three-column">
        <div class="column">
            <div class="info-row">
                <div class="info-label">Reason for Deviation:</div>
                <div class="info-value">N/A</div>
            </div>
        </div>
        <div class="column"></div>
        <div class="column"></div>
    </div>
</div>