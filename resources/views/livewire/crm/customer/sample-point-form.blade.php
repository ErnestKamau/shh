<div x-data="{
    initMap() {
        if (typeof google === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyBqS4AEZ-gVeXjG794Rh0eTd6yvdfMKTjg&sensor=false';
            script.async = true;
            script.defer = true;
            script.onload = () => this.renderMap();
            document.head.appendChild(script);
        } else {
            setTimeout(() => this.renderMap(), 100); // Small delay to ensure modal is visible
        }
    },
    renderMap() {
        let lat = parseFloat($wire.get('latitude')) || -1.2471255;
        let lng = parseFloat($wire.get('longitude')) || 36.7422618;
        
        let mapOptions = {
            center: { lat: lat, lng: lng },
            zoom: 12,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        let map = new google.maps.Map(document.getElementById('sample-point-map'), mapOptions);

        let marker = new google.maps.Marker({
            position: { lat: lat, lng: lng },
            map: map,
            draggable: true,
            title: 'Sample Point Location'
        });

        // Update Livewire on drag end
        google.maps.event.addListener(marker, 'dragend', function (event) {
            $wire.set('latitude', event.latLng.lat());
            $wire.set('longitude', event.latLng.lng());
        });

        // Click to move marker
        map.addListener('click', function (event) {
            marker.setPosition(event.latLng);
            $wire.set('latitude', event.latLng.lat());
            $wire.set('longitude', event.latLng.lng());
        });
        
        // Ensure map resizes correctly if modal animates
        google.maps.event.trigger(map, 'resize');
    },
    init() {
        this.initMap();
    }
}" x-init="init()">
    <template x-teleport="body">
        <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1"
            role="dialog" wire:click.self="close" wire:ignore.self>
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $pointId ? 'pencil' : 'plus' }}"></i>
                            {{ $pointId ? 'Edit' : 'Add' }} Sample Point
                        </h5>
                        <button type="button" class="close" wire:click="close" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="control-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    wire:model="name" placeholder="Sample Point Name..." required />
                                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label class="control-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                    wire:model="description" rows="3" placeholder="Sample Point Description..."></textarea>
                                @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label class="control-label">Company Unit <span class="text-danger">*</span></label>
                                <div class="tag-select-container @error('unitId') is-invalid @enderror"
                                    wire:click="$set('showUnitDropdown', true)"
                                    wire:click.outside="$set('showUnitDropdown', false)">
                                    <div class="tag-select-input">
                                        @if($this->selectedUnit)
                                            <span class="tag-badge">
                                                {{ $this->selectedUnit->name }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="clearUnit"></i>
                                            </span>
                                        @endif

                                        <input type="text"
                                            wire:model.live="unitSearch"
                                            class="tag-input"
                                            placeholder="{{ $this->selectedUnit ? '' : 'Search units...' }}"
                                            autocomplete="off">
                                    </div>

                                    @if($showUnitDropdown)
                                        <div class="tag-dropdown">
                                            @if(count($this->filteredUnits) > 0)
                                                @foreach($this->filteredUnits as $unit)
                                                    <div class="tag-dropdown-item" wire:click.stop="selectUnit({{ $unit->id }})">
                                                        {{ $unit->name }}
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="tag-dropdown-item text-muted">No units found</div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                @error('unitId') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label class="control-label">Location</label>
                                <div id="sample-point-map" style="width: 100%; height: 300px; border: 1px solid #ddd;"
                                    wire:ignore></div>
                                <small class="text-muted">Drag the marker or click on the map to set the
                                    location.</small>
                                @error('latitude') <div class="text-danger small">{{ $message }}</div> @enderror
                                @error('longitude') <div class="text-danger small">{{ $message }}</div> @enderror
                                <input type="hidden" id="point-lat">
                                <input type="hidden" id="point-lng">
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" wire:model="active"
                                    id="activePoint{{ $pointId ?? 'New' }}" />
                                <label class="form-check-label" for="activePoint{{ $pointId ?? 'New' }}">Is
                                    Active?</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save"></i> Save
                            </button>
                            <button type="button" class="btn btn-default" wire:click="close">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

</div>

<style>
    .tag-select-container {
        position: relative;
        width: 100%;
    }

    .tag-select-input {
        min-height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 4px 8px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        background-color: #fff;
    }

    .tag-input {
        border: none;
        outline: none;
        flex: 1;
        min-width: 120px;
        font-size: 0.9rem;
    }

    .tag-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f0f2f5;
        border-radius: 12px;
        padding: 2px 8px;
        font-size: 0.85rem;
    }

    .tag-badge i {
        cursor: pointer;
    }

    .tag-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        max-height: 220px;
        overflow-y: auto;
        z-index: 1100;
    }

    .tag-dropdown-item {
        padding: 8px 10px;
        cursor: pointer;
    }

    .tag-dropdown-item:hover {
        background: #f8f9fa;
    }

    .tag-select-container.is-invalid .tag-select-input {
        border-color: #dc3545;
    }
</style>