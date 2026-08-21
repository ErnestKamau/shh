{{--
	ls-card-stepper — vertical stepper + form panel (theme blues, not purple).
--}}
<div class="ls-card-stepper" x-data="{ step: 3 }">
	<ul class="ls-stepper">
		@foreach([
			1 => 'Organization Profile',
			2 => 'Territories and Sites',
			3 => 'Roles and Employees',
			4 => 'Products and Services',
			5 => 'Scheduler Settings',
		] as $n => $label)
			<li class="ls-stepper__item {{ $n < 3 ? 'is-done' : '' }} {{ $n === 3 ? 'is-active' : '' }}">
				<span class="ls-stepper__dot">{{ $n < 3 ? '✓' : $n }}</span>
				<p class="ls-stepper__label">{{ $label }}</p>
			</li>
		@endforeach
	</ul>
	<div class="ls-stepper-panel">
		<div class="ls-stepper-panel__progress"><span style="width:42%;"></span></div>
		<p class="small text-muted mb-1" style="font-size:0.7rem;">42% complete</p>
		<h4 class="ls-stepper-panel__title">Roles and Employees</h4>
		@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
			'label' => 'Organization Name',
			'name' => 'demo_org',
			'placeholder' => 'Acme Labs',
		])
		@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
			'label' => 'Organization Type',
			'name' => 'demo_org_type',
			'placeholder' => 'Testing laboratory',
		])
		<div class="row">
			<div class="col-6">
				@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
					'label' => 'Phone',
					'name' => 'demo_phone',
					'placeholder' => '+254 …',
				])
			</div>
			<div class="col-6">
				@include('layouts.lab.partials.ls-ui.fields.ls-field-text', [
					'label' => 'Email',
					'name' => 'demo_email_step',
					'placeholder' => 'lab@example.com',
				])
			</div>
		</div>
		<div class="ls-stepper-panel__actions">
			<button type="button" class="ls-btn" aria-label="Previous"><i class="mdi mdi-chevron-left"></i></button>
			<button type="button" class="ls-btn ls-btn--secondary-fill">Submit</button>
			<button type="button" class="ls-btn" aria-label="Next"><i class="mdi mdi-chevron-right"></i></button>
		</div>
	</div>
</div>
