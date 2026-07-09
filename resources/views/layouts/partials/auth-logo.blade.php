@php
    $loginLogo = optional(getActiveCompany())->logo ?: '/images/imara-sys.png';
@endphp
<img src="{{ $loginLogo }}"
     @if(!empty($authLogoClass)) class="{{ $authLogoClass }}" @endif
     @if(empty($authLogoClass)) style="max-width: 120px" @endif
     alt="{{ optional(getActiveCompany())->name ?? config('app.name') }}">
