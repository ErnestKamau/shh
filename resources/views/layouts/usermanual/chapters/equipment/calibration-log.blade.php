<h2>Calibration Log</h2>
<p>
	On an equipment profile, open the <strong>Calibration Log</strong> tab.
	Use it to record every calibration event, store the certificate, and keep correction factors and uncertainty on file.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/calibration-log.png',
	'caption' => 'Calibration Log tab — table of calibration entries and Add Calibration Log.',
])

<h2>What you see on the tab</h2>
<ul>
	<li>Search box for finding entries in the list.</li>
	<li><strong>Add Calibration Log</strong> — opens the create modal.</li>
	<li>Table columns typically include: <strong>Actions</strong>, <strong>Type</strong>, <strong>Service Provider</strong> (or performer), <strong>Date</strong>, <strong>Correction Factor</strong>, <strong>UoM</strong> (uncertainty of measure abbreviation), <strong>Certificate</strong>, <strong>Overseen By</strong>, <strong>Notes</strong>.</li>
</ul>

<h2>Row actions</h2>
<ul>
	<li><strong>Edit</strong> — open the same form with existing values (modal title <strong>Create/Edit Calibration Log</strong>).</li>
	<li><strong>Download</strong> — download the uploaded certificate file when present.</li>
	<li><strong>View</strong> — view notes / details.</li>
	<li><strong>Delete</strong> — remove the log after confirmation.</li>
</ul>

<h2>Add or edit a calibration — step by step</h2>
<ol>
	<li>Click <strong>Add Calibration Log</strong> (or Edit on a row).</li>
	<li>Fill the modal fields (required fields marked *):
		<ul>
			<li><strong>Date *</strong> — when calibration was performed.</li>
			<li><strong>Description *</strong> — what was done / scope.</li>
			<li><strong>Reference Number *</strong> — certificate or job reference.</li>
			<li><strong>Correction Factor *</strong> — numerical correction from the calibration.</li>
			<li><strong>Uncertainty of Measurement *</strong> — uncertainty value from the certificate.</li>
			<li><strong>Service Type</strong> — <strong>In House</strong> or <strong>External</strong>.
				<ul>
					<li>In House → pick the <strong>Employee</strong> who performed it.</li>
					<li>External → pick the <strong>Supplier</strong> / service provider.</li>
				</ul>
			</li>
			<li><strong>Certificate</strong> — optional file upload (keep within the allowed size, typically up to about 10 MB).</li>
			<li><strong>Notes *</strong> — free-text remarks.</li>
		</ul>
	</li>
	<li>Click <strong>Save</strong>, or <strong>Cancel</strong> to discard.</li>
</ol>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-lightbulb-on-outline"></i></span>
	<p>
		Calibration intervals and notification days are set on the asset in the create/edit wizard (Maintenance step).
		Recording a new calibration log is how you document that the work was actually done and keep the register auditable.
	</p>
</div>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-monitor-dashboard"></i></span>
	<p>
		Equipment Monitoring can use recent calibration information when templates run.
		Keep certificates and dates complete so monitoring and reports stay accurate.
	</p>
</div>
