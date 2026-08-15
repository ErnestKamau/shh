{{-- Country-filtered city tag-select. Expects: city_id, citySearch, showCityDropdown, selectedCity, filteredCities, country_id --}}
<div class="tag-select-container @error('city_id') is-invalid @enderror {{ empty($country_id) ? 'bg-light' : '' }}"
    wire:click="@if(!empty($country_id)) $set('showCityDropdown', true) @endif"
    wire:click.outside="$set('showCityDropdown', false)">
    <div class="tag-select-input">
        @if($this->selectedCity)
            <span class="tag-badge">
                {{ data_get($this->selectedCity, 'name') }}
                <i class="mdi mdi-close-circle" wire:click.stop="clearCity"></i>
            </span>
        @endif

        <input type="text"
            wire:model.live.debounce.200ms="citySearch"
            class="tag-input"
            placeholder="{{ empty($country_id) ? __('crm.select_country_first') : ($this->selectedCity ? '' : __('crm.search_cities')) }}"
            autocomplete="off"
            @disabled(empty($country_id))>
    </div>

    @if($showCityDropdown && !empty($country_id))
        <div class="tag-dropdown">
            @if(count($this->filteredCities) > 0)
                @foreach($this->filteredCities as $city)
                    <div class="tag-dropdown-item" wire:click.stop="selectCity('{{ data_get($city, 'id') }}')">
                        {{ data_get($city, 'name') }}
                    </div>
                @endforeach
            @else
                <div class="tag-dropdown-item text-muted">{{ __('crm.no_cities_found') }}</div>
            @endif
        </div>
    @endif
</div>
@error('city_id') <span class="text-danger">{{ $message }}</span> @enderror
