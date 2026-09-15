@props([
    'subject' => '',
])

<button
    type="button"
    {{ $attributes->merge(['class' => 'btn btn-add btn-sm']) }}
>
    <i class="mdi mdi-plus"></i>
    {{ __('crm.add') }} {{ $subject }}
</button>
