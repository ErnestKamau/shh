<div>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ $ratingHeader->name }}</h5>
            @if($ratingHeader->description)
                <small class="text-muted">{{ $ratingHeader->description }}</small>
            @endif
        </div>
        <div class="card-body">
            @if($ratingDetails->count() > 0)
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ratingSelect">Select Rating</label>
                            <select wire:model.live="selectedKey" class="form-control" id="ratingSelect">
                                <option value="">Choose a rating...</option>
                                @foreach($ratingDetails as $detail)
                                    <option value="{{ $detail->key }}">{{ $detail->key }} - {{ $detail->label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                @if($selectedKey)
                    <div class="mt-3">
                        <div class="alert alert-info">
                            <h6 class="mb-2">
                                <span class="badge badge-primary mr-2">{{ $selectedKey }}</span>
                                {{ $this->getLabel($selectedKey) }}
                            </h6>
                            <p class="mb-0">{{ $this->getInterpretation($selectedKey) }}</p>
                        </div>
                    </div>
                @endif

                <!-- Rating Details Table -->
                <div class="mt-4">
                    <h6>All Available Ratings</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead style="background-color: rgba(0, 0, 0, .03);">
                                <tr>
                                    <th>Key</th>
                                    <th>Label</th>
                                    <th>Interpretation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ratingDetails as $detail)
                                <tr>
                                    <td>
                                        <span class="badge badge-primary">{{ $detail->key }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $detail->label }}</strong>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $detail->interpretation }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="mdi mdi-star-outline fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0">No rating details available for this rating system.</p>
                </div>
            @endif
        </div>
    </div>
</div>