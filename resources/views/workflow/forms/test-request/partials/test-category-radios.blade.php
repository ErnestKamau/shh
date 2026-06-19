@php
    $rowIdx = $rowIdx ?? 0;
    $showLegionella = $showLegionella ?? true;
    $wirePrefix = $wirePrefix ?? 'formData.sample_rows.' . $rowIdx;
@endphp
<div class="custom-control custom-radio small mb-1">
    <input type="radio" id="cat_micro_{{ $rowIdx }}" name="test_category_{{ $rowIdx }}" wire:model="{{ $wirePrefix }}.test_category" value="microbiology" class="custom-control-input">
    <label class="custom-control-label" for="cat_micro_{{ $rowIdx }}">Microbiology</label>
</div>
@if($showLegionella)
    <div class="custom-control custom-radio small mb-1">
        <input type="radio" id="cat_leg_{{ $rowIdx }}" name="test_category_{{ $rowIdx }}" wire:model="{{ $wirePrefix }}.test_category" value="legionella" class="custom-control-input">
        <label class="custom-control-label" for="cat_leg_{{ $rowIdx }}">Legionella</label>
    </div>
@endif
<div class="custom-control custom-radio small">
    <input type="radio" id="cat_chem_{{ $rowIdx }}" name="test_category_{{ $rowIdx }}" wire:model="{{ $wirePrefix }}.test_category" value="chemistry" class="custom-control-input">
    <label class="custom-control-label" for="cat_chem_{{ $rowIdx }}">Chemistry</label>
</div>
