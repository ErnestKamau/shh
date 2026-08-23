@php
    $testInfo = $testInfo ?? [];
    $testLabel = (string) ($testInfo['test_label'] ?? 'Test');
    $fields = is_array($testInfo['fields'] ?? null) ? $testInfo['fields'] : [];
@endphp

@if($showTestInfoModal)
    <div class="modal fade show d-block integrity-test-info-modal" tabindex="-1" role="dialog" aria-modal="true" style="background: rgba(15, 23, 42, 0.45);">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-information-outline mr-1"></i>
                        {{ $testLabel }}
                    </h5>
                    <button type="button" class="close" wire:click="closeTestInfoModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if($fields === [])
                        <p class="text-muted mb-0">No technical details are available for this test.</p>
                    @else
                        <dl class="integrity-test-info-grid">
                            @foreach($fields as $label => $value)
                                @php
                                    $display = trim((string) $value);
                                @endphp
                                @if($display !== '')
                                    <div class="integrity-test-info-grid__item">
                                        <dt>{{ $label }}</dt>
                                        <dd>{{ $display }}</dd>
                                    </div>
                                @endif
                            @endforeach
                        </dl>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="closeTestInfoModal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif
