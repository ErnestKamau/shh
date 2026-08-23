<div class="workflow-stat-strip mb-3">
	<div class="workflow-stat-strip__item">
		<span class="workflow-stat-strip__dot workflow-stat-strip__dot--warning" aria-hidden="true"></span>
		<div>
			<p class="workflow-stat-strip__value">{{ $receivingDashboardStats['sub_contracting'] ?? 0 }}</p>
			<p class="workflow-stat-strip__label">Subcontracted</p>
			<p class="workflow-stat-strip__meta">Awaiting + dispatched</p>
		</div>
	</div>
	<div class="workflow-stat-strip__item">
		<span class="workflow-stat-strip__dot workflow-stat-strip__dot--neutral" aria-hidden="true"></span>
		<div>
			<p class="workflow-stat-strip__value">{{ $receivingDashboardStats['submitted'] ?? 0 }}</p>
			<p class="workflow-stat-strip__label">Submitted</p>
			<p class="workflow-stat-strip__meta">Awaiting enquiry</p>
		</div>
	</div>
	<div class="workflow-stat-strip__item">
		<span class="workflow-stat-strip__dot workflow-stat-strip__dot--info" aria-hidden="true"></span>
		<div>
			<p class="workflow-stat-strip__value">{{ $receivingDashboardStats['ready_for_reception'] ?? 0 }}</p>
			<p class="workflow-stat-strip__label">Ready for reception</p>
			<p class="workflow-stat-strip__meta">Awaiting acceptance</p>
		</div>
	</div>
	<div class="workflow-stat-strip__item">
		<span class="workflow-stat-strip__dot workflow-stat-strip__dot--success" aria-hidden="true"></span>
		<div>
			<p class="workflow-stat-strip__value">{{ $receivingDashboardStats['accepted'] ?? 0 }}</p>
			<p class="workflow-stat-strip__label">Accepted</p>
			<p class="workflow-stat-strip__meta">Jobs created</p>
		</div>
	</div>
	<div class="workflow-stat-strip__item">
		<span class="workflow-stat-strip__dot workflow-stat-strip__dot--primary" aria-hidden="true"></span>
		<div>
			<p class="workflow-stat-strip__value">{{ $receivingDashboardStats['todays_check_ins'] ?? 0 }}</p>
			<p class="workflow-stat-strip__label">Today&rsquo;s acceptances</p>
			<p class="workflow-stat-strip__meta">Accepted today</p>
		</div>
	</div>
</div>
