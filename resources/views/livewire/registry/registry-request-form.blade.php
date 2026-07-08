<div class="container-fluid">
    @include('layouts.registry.partials.page-header', [
        'title' => 'Register New Request',
        'description' => 'Capture a new correspondence request and route it through the appropriate workflow.',
        'icon' => 'mdi-plus-circle',
    ])

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <form wire:submit="save">
                        <div class="form-group">
                            <label class="fw-bold">Category *</label>
                            <select wire:model.live="request_category_id" class="form-control no-select2">
                                <option value="">Select...</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('request_category_id')<span class="text-danger d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label class="fw-bold">Subject *</label>
                            <input wire:model="subject" class="form-control">
                            @error('subject')<span class="text-danger d-block">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label class="fw-bold">Description</label>
                            <textarea wire:model="description" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="fw-bold">Priority</label>
                                <select wire:model="priority" class="form-control no-select2">
                                    <option value="low">Low</option>
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="fw-bold">Direction</label>
                                <select wire:model="direction" class="form-control no-select2">
                                    <option value="incoming">Incoming</option>
                                    <option value="outgoing">Outgoing</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="fw-bold">Submitting Party</label>
                                <input wire:model="submitting_party" class="form-control">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save"></i> Register Request
                        </button>
                        <a href="{{ route('registry.requests.index') }}" class="btn btn-outline-secondary ml-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
