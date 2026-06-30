<div class="row mb-3">
	<div class="col-12">
		<div class="workflow-kpi-grid">
			<div class="workflow-kpi-card">
				<div class="workflow-kpi-card__label">Sub-contracting</div>
				<div class="workflow-kpi-card__value">{{ $receivingDashboardStats['sub_contracting'] ?? 0 }}</div>
				<div class="workflow-kpi-card__subtitle">Awaiting + dispatched</div>
			</div>
			<div class="workflow-kpi-card">
				<div class="workflow-kpi-card__label">Submitted</div>
				<div class="workflow-kpi-card__value">{{ $receivingDashboardStats['submitted'] ?? 0 }}</div>
				<div class="workflow-kpi-card__subtitle">Awaiting enquiry</div>
			</div>
			<div class="workflow-kpi-card">
				<div class="workflow-kpi-card__label">Ready for reception</div>
				<div class="workflow-kpi-card__value">{{ $receivingDashboardStats['ready_for_reception'] ?? 0 }}</div>
				<div class="workflow-kpi-card__subtitle">Awaiting check-in</div>
			</div>
			<div class="workflow-kpi-card">
				<div class="workflow-kpi-card__label">Received</div>
				<div class="workflow-kpi-card__value">{{ $receivingDashboardStats['received'] ?? 0 }}</div>
				<div class="workflow-kpi-card__subtitle">At lab, pre-review</div>
			</div>
			<div class="workflow-kpi-card">
				<div class="workflow-kpi-card__label">Today&rsquo;s check-ins</div>
				<div class="workflow-kpi-card__value">{{ $receivingDashboardStats['todays_check_ins'] ?? 0 }}</div>
				<div class="workflow-kpi-card__subtitle">Checked in today</div>
			</div>
		</div>
	</div>
</div>
