@php
    $themeVars = \App\Services\System\ThemeService::resolvedVariables();
@endphp
<style>
    :root {
        --color-primary: {{ $themeVars['primary'] }};
        --color-primary-hover: {{ $themeVars['secondary'] }};
        --color-primary-soft: {{ $themeVars['primary_soft'] }};
        --color-primary-border-soft: {{ $themeVars['primary_border_soft'] }};
        --color-primary-tint: {{ $themeVars['primary_tint'] }};
        --color-bg-app: #f8fafc;
    }
</style>
