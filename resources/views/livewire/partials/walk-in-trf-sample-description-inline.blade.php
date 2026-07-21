@php
    $rowIdx = $rowIdx ?? 0;
    $wirePrefix = $wirePrefix ?? ('formData.sample_description.'.$rowIdx);
    $editorId = 'rft-desc-inline-'.$rowIdx.'-'.Str::random(4);
    $html = (string) ($formData['sample_description'][$rowIdx] ?? '');
@endphp

{{-- wire:ignore is required for TinyMCE (rich editor plugin lifecycle). --}}
<div
    class="rft-sample-description"
    wire:ignore
    x-data="rftSampleDescriptionEditor({
        editorId: @js($editorId),
        wireKey: @js($wirePrefix),
        rowIndex: {{ (int) $rowIdx }},
    })"
    x-on:trf-destroy-editors.window="destroy()"
    x-on:rft-sample-card-hidden.window="if ($event.detail.row === rowIndex) destroy()"
    x-on:rft-sample-card-shown.window="if ($event.detail.row === rowIndex) { destroy(); $nextTick(() => mountEditor()); }"
>
    <textarea id="{{ $editorId }}" class="form-control">{!! $html !!}</textarea>
</div>
