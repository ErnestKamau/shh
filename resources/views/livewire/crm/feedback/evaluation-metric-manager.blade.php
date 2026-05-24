<div>
    @php
        $breadcrumbItems = [
            ['link' => route('customers-list'), 'name' => __('crm.module_name'), 'icon' => null],
            ['link' => route('feedback-home'), 'name' => __('crm.customer_feedback'), 'icon' => null],
            ['link' => '#', 'name' => __('crm.configuration'), 'icon' => null]
        ];
    @endphp
    <main>
        <div class="container-fluid">
            <x-crm.page-header
                :breadcrumbItems="$breadcrumbItems"
                :title="__('crm.feedback_configuration')"
                :subtitle="__('crm.feedback_configuration_subtitle')"
                icon="mdi-cog-refresh-outline"
            >
                <x-slot:actions>
                    <button type="button" class="btn btn-add btn-sm crm-btn-add" wire:click="create">
                        <i class="mdi mdi-plus"></i> {{ __('crm.add_new_metric') }}
                    </button>
                </x-slot:actions>
            </x-crm.page-header>

            <div class="crm-card border-0 shadow-sm overflow-hidden" style="border-radius: var(--crm-radius-lg);">
                <x-crm.data-table class="rounded-0 border-0 crm-loading-overlay" wire:loading.class="opacity-50">
                    <x-slot:header>
                                <tr>
                                    <th class="px-4">{{ __('crm.metric_name') }}</th>
                                    <th class="text-center">{{ __('crm.scale_max') }}</th>
                                    <th class="text-center">{{ __('crm.order') }}</th>
                                    <th class="text-center">{{ __('crm.status') }}</th>
                                    <th class="text-right px-4">{{ __('crm.actions') }}</th>
                                </tr>
                    </x-slot:header>
                                @forelse($metrics as $metric)
                                    <tr wire:key="metric-{{ $metric->id }}">
                                        <td class="px-4">
                                            <span class="font-weight-bold text-dark">{{ $metric->name }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="crm-badge crm-badge-neutral px-3">1 -
                                                {{ $metric->max_rating }}</span>
                                        </td>
                                        <td class="text-center">
                                            {{ $metric->display_order }}
                                        </td>
                                        <td class="text-center">
                                            @if($metric->is_active)
                                                <span class="crm-badge crm-badge-success cursor-pointer"
                                                    wire:click="toggleStatus({{ $metric->id }})">{{ ucfirst(__('crm.active')) }}</span>
                                            @else
                                                <span class="crm-badge crm-badge-danger cursor-pointer"
                                                    wire:click="toggleStatus({{ $metric->id }})">{{ __('crm.inactive') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-right px-4">
                                            <x-crm.action-buttons>
                                                <button type="button" class="btn crm-btn crm-btn-edit btn-sm" wire:click="edit({{ $metric->id }})" title="{{ __('crm.edit') }}">
                                                    <i class="mdi mdi-pencil-outline"></i>
                                                </button>
                                                <button type="button" class="btn crm-btn crm-btn-delete btn-sm ml-1" 
                                                    wire:confirm="Are you sure you want to delete this metric? This action cannot be undone." 
                                                    wire:click="deleteMetric({{ $metric->id }})" title="{{ __('crm.delete') }}">
                                                    <i class="mdi mdi-trash-can-outline"></i>
                                                </button>
                                            </x-crm.action-buttons>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <x-crm.empty-state
                                                icon="mdi-information-outline"
                                                message="No evaluation metrics found"
                                                help="Get started by adding your first evaluation metric."
                                            />
                                        </td>
                                    </tr>
                                @endforelse
                </x-crm.data-table>

                <x-crm.pagination :summary="'Showing ' . ($metrics->firstItem() ?? 0) . ' to ' . ($metrics->lastItem() ?? 0) . ' of ' . $metrics->total() . ' results'">
                    {{ $metrics->links() }}
                </x-crm.pagination>
            </div>
        </div>
    </main>

    <!-- Modal -->
    <div class="modal fade @if($showModal) show @endif"
        style="@if($showModal) display: block; background: rgba(0,0,0,0.5); @endif" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <form wire:submit.prevent="save" class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pt-4 px-4">
                        <h5 class="modal-title font-weight-bold">
                            {{ $editingMetricId ? __('crm.edit') . ' ' . __('crm.metric_name') : __('crm.add_new_metric') }}
                        </h5>
                        <button type="button" class="close" wire:click="$set('showModal', false)">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body px-4 pb-4">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-muted text-uppercase mb-1">{{ __('crm.metric_name') }}</label>
                            <input type="text" wire:model="name" class="form-control rounded-sm border-light bg-light"
                                placeholder="e.g. Communication, Lab Processes">
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-muted text-uppercase mb-1">Prompt Text (Description)</label>
                            <textarea wire:model="prompt_text" class="form-control rounded-sm border-light bg-light" rows="2"
                                placeholder="The question or explanation shown to the customer..."></textarea>
                            @error('prompt_text') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted text-uppercase mb-1">
                                        Max Rating Scale
                                        <span class="font-weight-normal text-lowercase" style="font-size: 0.7rem;">(max 10)</span>
                                    </label>
                                    <input type="number" wire:model.live="max_rating"
                                        class="form-control rounded-sm border-light bg-light" min="1" max="10"
                                        x-data x-on:focus="$el.select()">
                                    @error('max_rating') <span class="text-danger small">{{ $message }}</span> @enderror
                                    <small class="text-muted italic d-block">e.g. 1 = Poor, 5 = Outstanding</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted text-uppercase mb-1">Display
                                        Order</label>
                                    <input type="number" wire:model="display_order"
                                        class="form-control rounded-sm border-light bg-light">
                                    @error('display_order') <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-muted text-uppercase mb-2">Rating Labels</label>
                            <div class="bg-light p-3 rounded" style="border: 1px dashed #e2e8f0;">
                                <div class="row">
                                    @for($i = 1; $i <= $max_rating; $i++)
                                        <div class="col-6 mb-2">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span
                                                        class="input-group-text bg-white border-light font-weight-bold text-primary">{{ $i }}</span>
                                                </div>
                                                <input type="text" wire:model="rating_labels.{{ $i }}"
                                                    class="form-control border-light" placeholder="Label for {{ $i }}">
                                            </div>
                                            @error('rating_labels.' . $i) <span
                                            class="text-danger extra-small">{{ $message }}</span> @enderror
                                        </div>
                                    @endfor
                                </div>
                                <small class="text-muted italic d-block mt-2" style="font-size: 0.7rem;">Define what
                                    each score level means (e.g., 1 = Poor, 5 = Excellent).</small>
                            </div>
                        </div>

                        <div class="custom-control custom-switch mt-2">
                            <input type="checkbox" wire:model="is_active" class="custom-control-input"
                                id="metricActiveStatus">
                            <label class="custom-control-label font-weight-600" for="metricActiveStatus">Active
                                Status</label>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 px-4 pb-4">
                        <button type="button" class="btn btn-light px-4"
                            wire:click="$set('showModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            {{ $editingMetricId ? 'Save Changes' : 'Create Metric' }}
                        </button>
                    </div>
            </form>
        </div>
    </div>
</div>
</div>