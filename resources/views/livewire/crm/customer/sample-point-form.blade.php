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
            title: 'Sampling Location'
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
        <div class="modal fade show sample-point-modal-overlay" style="display: block; background-color: rgba(0,0,0,0.5); overflow-y: auto;"
            tabindex="-1" role="dialog" wire:click.self="close" wire:ignore.self>
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable sample-point-modal-dialog" role="document">
                <div class="modal-content sample-point-modal-content">
                    <div class="modal-header sample-point-modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $pointId ? 'pencil' : 'plus' }}"></i>
                            {{ $pointId ? 'Edit' : 'Add' }} Sampling Location
                        </h5>
                        <button type="button" class="close" wire:click="close" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form wire:submit.prevent="save" class="sample-point-modal-form">
                        <div class="modal-body sample-point-modal-body">
                            <x-imara.form-field label="{{ __('crm.name') }}" :required="true" :error="$errors->first('name')">
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    wire:model="name" placeholder="{{ __('crm.enter_sample_point_name') }}" required />
                            </x-imara.form-field>
                            <x-imara.form-field label="{{ __('crm.description') }}" :error="$errors->first('description')">
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                    wire:model="description" rows="3" placeholder="{{ __('crm.description') }}..."></textarea>
                            </x-imara.form-field>
                            <x-imara.form-field label="{{ __('crm.company_unit') }}" :required="true" :error="$errors->first('unitId')">
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
                                                    <div class="tag-dropdown-item" wire:click.stop="selectUnit('{{ $unit->id }}')">
                                                        {{ $unit->name }}
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="tag-dropdown-item text-muted">No units found</div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </x-imara.form-field>

                            <x-imara.form-field label="{{ __('crm.contact_details') }}" :error="$errors->first('contactId')">
                                <div class="tag-select-container @error('contactId') is-invalid @enderror"
                                    wire:click="$set('showContactDropdown', true)"
                                    wire:click.outside="$set('showContactDropdown', false)">
                                    <div class="tag-select-input">
                                        @if($this->selectedContact)
                                            <span class="tag-badge">
                                                {{ $this->formatContactLabel($this->selectedContact) }}
                                                <i class="mdi mdi-close-circle" wire:click.stop="clearContact"></i>
                                            </span>
                                        @endif

                                        <input type="text"
                                            wire:model.live="contactSearch"
                                            class="tag-input"
                                            placeholder="{{ $this->selectedContact ? '' : 'Select contact details...' }}"
                                            autocomplete="off">
                                    </div>

                                    @if($showContactDropdown)
                                        <div class="tag-dropdown">
                                            @if(count($this->filteredContacts) > 0)
                                                @foreach($this->filteredContacts as $contact)
                                                    <div class="tag-dropdown-item" wire:click.stop="selectContact('{{ $contact->id }}')">
                                                        <div class="fw-semibold">{{ $this->formatContactLabel($contact) }}</div>
                                                        <small class="text-muted">
                                                            {{ $contact->email ?: __('crm.not_available') }}
                                                            @if($contact->telephone)
                                                                · {{ $contact->telephone }}
                                                            @endif
                                                        </small>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="tag-dropdown-item text-muted">{{ __('crm.no_contacts_found') }}</div>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                @if($this->selectedContact)
                                    <div class="mt-2 p-2 rounded border bg-light">
                                        <small class="d-block text-muted mb-1">Contact Details</small>
                                        <div class="small">
                                            <div><strong>{{ $this->formatContactLabel($this->selectedContact) }}</strong></div>
                                            @if($this->selectedContact->email)
                                                <div><i class="mdi mdi-email-outline mr-1"></i>{{ $this->selectedContact->email }}</div>
                                            @endif
                                            @if($this->selectedContact->telephone)
                                                <div><i class="mdi mdi-phone-outline mr-1"></i>{{ $this->selectedContact->telephone }}</div>
                                            @endif
                                            @if($this->selectedContact->mobile)
                                                <div><i class="mdi mdi-cellphone mr-1"></i>{{ $this->selectedContact->mobile }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </x-imara.form-field>

                            <x-imara.form-field label="Location" class="mb-0">
                                <div id="sample-point-map" class="sample-point-map" wire:ignore></div>
                                <small class="text-muted">Drag the marker or click on the map to set the
                                    location.</small>
                                @error('latitude') <div class="text-danger small">{{ $message }}</div> @enderror
                                @error('longitude') <div class="text-danger small">{{ $message }}</div> @enderror
                                <input type="hidden" id="point-lat">
                                <input type="hidden" id="point-lng">
                            </x-imara.form-field>
                            <div class="form-group">
                                <x-imara.custom-checkbox wire:model="active" label="{{ __('crm.is_active') }}" :id="'activePoint' . ($pointId ?? 'New')" />
                            </div>
                        </div>
                        <div class="modal-footer sample-point-modal-footer">
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

    <style>
        .sample-point-modal-overlay {
            z-index: 1060;
            padding: 1rem 0;
        }

        .sample-point-modal-dialog {
            max-width: 720px;
            margin: 1rem auto;
        }

        .sample-point-modal-content {
            max-height: calc(100vh - 2rem);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sample-point-modal-header {
            flex-shrink: 0;
        }

        .sample-point-modal-form {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
        }

        .sample-point-modal-body {
            flex: 1 1 auto;
            overflow-y: auto;
            min-height: 0;
        }

        .sample-point-modal-footer {
            flex-shrink: 0;
            position: sticky;
            bottom: 0;
            background: #fff;
            border-top: 1px solid #dee2e6;
            z-index: 2;
        }

        .sample-point-map {
            width: 100%;
            height: 220px;
            border: 1px solid #ddd;
            border-radius: 0.25rem;
        }

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
            background: #e8d5d9;
            color: #4a1520;
            border: 1px solid #c49aa3;
            border-radius: 999px;
            padding: 3px 10px;
            font-size: 0.85rem;
            font-weight: 600;
            line-height: 1.3;
        }

        .tag-badge i {
            cursor: pointer;
            color: #7a1f2b;
            font-size: 1rem;
        }

        .tag-badge i:hover {
            color: #4a1520;
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
</div>