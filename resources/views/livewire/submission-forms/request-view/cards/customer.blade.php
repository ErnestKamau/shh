@php
    $tint = 'white';
@endphp
<article class="rv-card rv-card--{{ $tint }}">
    <header class="rv-card-header">
        <span class="rv-card-tag">Customer</span>
        <i class="mdi mdi-domain rv-card-header-icon" aria-hidden="true"></i>
    </header>
    <div class="rv-card-body">
        @php
            $nameField = collect($customerCard['fields'] ?? [])->first(
                fn ($f) => in_array(strtolower((string) ($f['name'] ?? '')), ['customer_name', 'name'], true)
            );
            $otherFields = collect($customerCard['fields'] ?? [])->reject(
                fn ($f) => in_array(strtolower((string) ($f['name'] ?? '')), ['customer_name', 'name'], true)
            );
        @endphp

        @if($nameField)
            <h3 class="rv-card-title">{{ $nameField['value'] }}</h3>
        @elseif(($customerCard['contact']['name'] ?? null))
            <h3 class="rv-card-title">{{ $customerCard['contact']['name'] }}</h3>
        @else
            <h3 class="rv-card-title">Customer</h3>
        @endif

        <ul class="rv-field-list">
            @foreach($otherFields as $field)
                <li class="rv-field-item">
                    <i class="mdi {{ $field['icon'] }}" aria-hidden="true"></i>
                    <div>
                        <span class="rv-field-label">{{ $field['label'] }}</span>
                        <span class="rv-field-value">{{ $field['value'] }}</span>
                    </div>
                </li>
            @endforeach
        </ul>

        @if(!empty($customerCard['contact']) && (
            ($customerCard['contact']['name'] ?? null)
            || ($customerCard['contact']['email'] ?? null)
            || ($customerCard['contact']['phone'] ?? null)
        ))
            <div class="rv-contact-block">
                <span class="rv-contact-heading">Contact</span>
                <ul class="rv-field-list rv-field-list--compact">
                    @if($customerCard['contact']['name'] ?? null)
                        <li class="rv-field-item">
                            <i class="mdi mdi-account-outline" aria-hidden="true"></i>
                            <div>
                                <span class="rv-field-label">Name</span>
                                <span class="rv-field-value">{{ $customerCard['contact']['name'] }}</span>
                            </div>
                        </li>
                    @endif
                    @if($customerCard['contact']['email'] ?? null)
                        <li class="rv-field-item">
                            <i class="mdi mdi-email-outline" aria-hidden="true"></i>
                            <div>
                                <span class="rv-field-label">Email</span>
                                <span class="rv-field-value">{{ $customerCard['contact']['email'] }}</span>
                            </div>
                        </li>
                    @endif
                    @if($customerCard['contact']['phone'] ?? null)
                        <li class="rv-field-item">
                            <i class="mdi mdi-phone-outline" aria-hidden="true"></i>
                            <div>
                                <span class="rv-field-label">Phone</span>
                                <span class="rv-field-value">{{ $customerCard['contact']['phone'] }}</span>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
        @endif
    </div>
</article>
