@php
    $wirePrefix = $wirePrefix ?? ('formData.'.($name ?? 'rich_text'));
    $editorId = $editorId ?? ('sf-rich-'.preg_replace('/[^a-zA-Z0-9_-]/', '_', (string) ($fieldId ?? 'field')).'-'.uniqid());
    $html = (string) ($value ?? '');
    $height = (int) ($height ?? 180);
@endphp

{{-- wire:ignore is required for TinyMCE (rich editor plugin lifecycle). --}}
<div
    class="sf-rich-text-livewire"
    wire:ignore
    x-data="rftSampleDescriptionEditor({
        editorId: @js($editorId),
        wireKey: @js($wirePrefix),
        rowIndex: {{ (int) ($rowIndex ?? 0) }},
    })"
    x-init="$watch('$wire.{{ $wirePrefix }}', () => {})"
>
    <textarea id="{{ $editorId }}" class="form-control">{!! $html !!}</textarea>
</div>
