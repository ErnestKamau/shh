<div>
    <form wire:submit.prevent="submit">
        @if(session()->has('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @foreach($template->sections as $section)
            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h5>{{ $section->title }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($section->fields as $field)
                            @include('template-engine::livewire.partials.render-field', ['field' => $field])
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach

        <div class="text-right mt-3">
            <button type="submit" class="btn btn-success btn-lg">Submit Form</button>
        </div>
    </form>
</div>
