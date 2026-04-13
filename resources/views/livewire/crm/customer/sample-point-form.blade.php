<div x-data="{
    initSelect2() {
        setTimeout(() => {
            let unitSelect = $('#unit-select-sample');
            if (unitSelect.hasClass('select2-hidden-accessible')) {
                unitSelect.select2('destroy');
            }
            unitSelect.select2({
                placeholder: 'Select Unit',
                allowClear: true,
                width: '100%',
                dropdownParent: unitSelect.closest('.modal'),
                closeOnSelect: true
            }).on('change', function (e) {
                var data = $(this).val();
                $wire.set('unitId', data);
            });

            // Initial load
            let initialUnit = $wire.get('unitId');
            if (initialUnit) {
                unitSelect.val(initialUnit).trigger('change');
            }
        }, 100);
    },
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
        this.initSelect2();
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
                                <label class="control-label">Company Unit <span class="text-danger">*</span></label>
                                <div wire:ignore>
                                    <select id="unit-select-sample" class="form-control" required>
                                        <option value="">Select Unit</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
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