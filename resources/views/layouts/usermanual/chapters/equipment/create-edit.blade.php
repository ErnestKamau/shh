<h2>Create &amp; edit equipment</h2>
<p>
	From <strong>Equipment List</strong>, click <strong>Add Equipment</strong> (create) or a row’s <strong>Edit</strong> action.
	The same five-step wizard opens as <strong>Create Equipment</strong> or <strong>Edit Equipment</strong>.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/create-edit-wizard.png',
	'caption' => 'Create Equipment wizard — five steps: Basic Info, Assignment, Monitoring, Maintenance, Depreciation.',
])

<h2>Wizard navigation</h2>
<ul>
	<li>Step circles: <strong>1 Basic Info</strong> → <strong>2 Assignment</strong> → <strong>3 Monitoring</strong> → <strong>4 Maintenance</strong> → <strong>5 Depreciation</strong>. You can click a completed step to jump back.</li>
	<li><strong>Previous</strong> — go back one step.</li>
	<li><strong>Next</strong> — validate the current step and move forward.</li>
	<li><strong>Save</strong> — appears on the last step (or when editing as allowed) to store the equipment.</li>
	<li><strong>Cancel</strong> — close without saving.</li>
</ul>
<p>Fields marked with a red asterisk (<span class="text-danger">*</span>) are required.</p>

<h2>Step 1 — Basic Info</h2>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/create-edit-wizard.png',
	'caption' => 'Step 1 — Basic Information and Photo.',
])

<h3>Basic Information</h3>
<ul>
	<li><strong>Name *</strong> — everyday name of the instrument.</li>
	<li><strong>Equipment ID *</strong> — unique equipment number.</li>
	<li><strong>Description *</strong> — short description of what it is / what it is used for.</li>
	<li><strong>Equipment has logbook tracking</strong> — when ticked, a <strong>Log Book</strong> tab appears on the profile to configure columns and capture logs.</li>
	<li><strong>Photo</strong> — optional image file. On edit you may see the current photo.</li>
</ul>
<h3>Specifications</h3>
<ul>
	<li><strong>Make *</strong> / <strong>Model *</strong></li>
	<li><strong>Serial Number</strong> / <strong>Barcode Number</strong> / <strong>Manufacturer</strong></li>
</ul>
<h3>Procurement &amp; Technical Lifecycle</h3>
<ul>
	<li><strong>Purchase Price</strong> / <strong>Supplier Name</strong></li>
	<li><strong>Installation Date</strong> / <strong>Commissioning Date</strong></li>
	<li><strong>Detection Limit</strong> / <strong>Tolerance Limit</strong></li>
	<li><strong>Warranty Duration / Info</strong> / <strong>Operating Environment</strong></li>
	<li><strong>End of Life</strong> / <strong>End of Service</strong></li>
</ul>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/create-edit-procurement.png',
	'caption' => 'Procurement & Technical Lifecycle — purchase price, supplier, installation, limits, warranty, end of life/service.',
])

<h2>Step 2 — Assignment</h2>
<p>Section title on screen: <strong>Assignment &amp; Location</strong>.</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/create-edit-assignment.png',
	'caption' => 'Step 2 — Assignment & Location (Status, Condition, Department, Employee, Asset Type/Location, Lab).',
])
<ul>
	<li><strong>Status *</strong> — typically Active, Obsolete, or Out Of Service.</li>
	<li><strong>Condition *</strong> — free-text condition (for example Good).</li>
	<li><strong>Warranty Date *</strong> / <strong>Date Purchased</strong></li>
	<li><strong>Department *</strong> — search and select; clear with the badge ×.</li>
	<li><strong>Assigned Employee</strong> — optional search and select.</li>
	<li><strong>Asset Type</strong> / <strong>Asset Location</strong> — from master data (see Asset Types &amp; Locations).</li>
	<li><strong>Lab</strong> — required when daily log is enabled; also needed so the unit can appear under labs in monitoring templates. Hint on screen: <em>Required for monitoring templates so the unit appears under the selected labs.</em></li>
	<li><strong>Active</strong> — checkbox <strong>Mark This Equipment As Active</strong>.</li>
</ul>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-alert-circle-outline"></i></span>
	<p>
		If you enable Requires Daily Log on step 3 without choosing a Lab on step 2,
		you will see an error and a <strong>Go to Assignment</strong> button to fix it.
	</p>
</div>

<h2>Step 3 — Monitoring</h2>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/create-edit-monitoring.png',
	'caption' => 'Step 3 — Requires Daily Log and Daily Log Configuration (Logging Frequency).',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/create-edit-daily-log-frequency.png',
	'caption' => 'Frequency Schedule — e.g. Twice a day with morning / evening labels.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/create-edit-value-types.png',
	'caption' => 'Value Types — Add Value Type, then choose Constant/Range and Nature of Result.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/create-edit-value-types-filled.png',
	'caption' => 'Value Type filled — Constant, Quantitative, Expected Value (30), Reporting Unit (g/L).',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/create-edit-value-types-range.png',
	'caption' => 'Value Type as Range — Minimum / Maximum values (always quantitative) and Reporting Unit.',
])

<ul>
	<li><strong>Requires Daily Log</strong> — checkbox labelled <strong>Equipment Appears On Daily Log Page</strong>.</li>
</ul>
<p>When daily log is on, configure:</p>
<ul>
	<li><strong>Logging Frequency *</strong> — Once a day through Six times a day.</li>
	<li><strong>Frequency Schedule</strong> — table of Frequency ID + Label (type a label for each slot).</li>
	<li><strong>Logging Monitored By Another Equipment</strong> — <strong>Enable monitored equipment</strong>, then pick <strong>Monitored Equipment (Equipment ID) *</strong>.</li>
	<li><strong>Value Types *</strong> — click <strong>Add Value Type</strong> (at least one required). For each card:
		<ul>
			<li><strong>Value Type *</strong> — Constant or Range.</li>
			<li><strong>Nature of Result *</strong> — Qualitative or Quantitative (Range is always quantitative).</li>
			<li><strong>Expected Value *</strong> — for Constant; or <strong>Minimum Value *</strong> / <strong>Maximum Value *</strong> for Range.</li>
			<li><strong>Reporting Unit</strong> — unit of measurement for the recorded value.</li>
			<li><strong>Remove</strong> — delete that value type card.</li>
		</ul>
	</li>
</ul>

<h2>Step 4 — Maintenance</h2>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/create-edit-maintenance.png',
	'caption' => 'Step 4 — Maintenance Schedule and Calibration Schedule (days and notification days).',
])

<p>Section: <strong>Maintenance Schedule</strong></p>
<ul>
	<li><strong>Maintenance After (Days) *</strong> — interval between maintenance services.</li>
	<li><strong>Maintenance Notification (Days) *</strong> — how many days before due to warn users.</li>
</ul>
<p>Section: <strong>Preventive Maintenance Schedule</strong> (when shown)</p>
<ul>
	<li><strong>Preventive Maintenance Period *</strong></li>
	<li><strong>Preventive Maintenance Notification Days *</strong></li>
</ul>
<p>Section: <strong>Calibration Schedule</strong></p>
<ul>
	<li><strong>Calibration After Days *</strong></li>
	<li><strong>Calibration Notification Days *</strong> (label may read as Calibration Notification in days).</li>
</ul>

<h2>Step 5 — Depreciation</h2>
<p>Section: <strong>Asset Depreciation Configuration</strong></p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/depreciation-and-reports.png',
	'caption' => 'Step 5 — Enable Depreciation, currency, capitalized amount, method, and frequencies.',
])

<ul>
	<li><strong>Enable Depreciation</strong> — switch. When off, you can save without depreciation fields.</li>
</ul>
<p>When enabled:</p>
<ul>
	<li><strong>Financial Basis</strong> — <strong>Currency *</strong>, <strong>Freight / Installation Cost</strong>, <strong>Capitalized Amount</strong> (purchase price + freight; may be read-only unless override is allowed).</li>
	<li><strong>Depreciation Method</strong> — <strong>Method *</strong>, <strong>Depreciation Frequencies *</strong>, and method-specific fields (useful life, residual value, rates) as shown for the selected method.</li>
</ul>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-lightbulb-on-outline"></i></span>
	<p>
		After Save, open the asset from the list to add calibration, verification, maintenance logs,
		operators, and attachments. Depreciation can also be reviewed later under Asset Depreciation on the profile and in the sidebar.
	</p>
</div>
