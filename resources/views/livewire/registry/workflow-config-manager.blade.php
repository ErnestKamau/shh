<div class="container-fluid">
    @include('layouts.registry.partials.page-header', [
        'title' => 'Workflow Configuration',
        'description' => 'View workflow definitions and step sequences for registry request categories.',
        'icon' => 'mdi-sitemap',
    ])

    <div class="row">
        <div class="col-12">
            @forelse($definitions as $def)
                <div class="card shadow-sm border-0 mb-3" style="border-radius: 15px;">
                    <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                        <h6 class="mb-0">
                            <i class="mdi mdi-sitemap text-primary"></i>
                            {{ $def->name }}
                            <code class="ml-2">{{ $def->code }}</code>
                        </h6>
                    </div>
                    <ul class="list-group list-group-flush">
                        @foreach($def->steps as $step)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <strong>{{ $step->sequence }}.</strong> {{ $step->step_name }}
                                    <code class="ml-2">{{ $step->step_code }}</code>
                                </span>
                                <span>
                                    @if($step->role_name)
                                        <span class="badge badge-light">{{ $step->role_name }}</span>
                                    @endif
                                    @if($step->is_final)
                                        <span class="badge badge-success">Final</span>
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-body text-center text-muted py-5">
                        <i class="mdi mdi-sitemap" style="font-size: 3rem;"></i>
                        <p class="mb-0 mt-3">No workflow definitions configured.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
