<h2>Equipment Daily Log</h2>
<p>
	Open <strong>Equipment → Equipment Daily Log</strong>.
	Page title: <strong>Equipment Daily Log</strong>.
	This page lists equipment that must be logged on a given day (on/off times and analyst).
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/daily-log.png',
	'caption' => 'Equipment Daily Log — date picker and daily usage table.',
])

<h2>How equipment appears here</h2>
<p>
	On create/edit (wizard step <strong>Monitoring</strong>), tick <strong>Requires Daily Log</strong>
	(<strong>Equipment Appears On Daily Log Page</strong>), choose a <strong>Lab</strong> on Assignment,
	set <strong>Logging Frequency</strong>, frequency labels, and at least one <strong>Value Type</strong>.
	Only those assets show on this page.
</p>

<h2>Using the daily log page</h2>
<ol>
	<li>Set <strong>Date:</strong> to the day you are reviewing or capturing.</li>
	<li>Section <strong>Daily Equipment Usage</strong> — subtitle explains it shows equipment switched on and off for the selected date.</li>
	<li>Table columns: <strong>#</strong>, <strong>Name</strong>, <strong>Equipment Number</strong>, <strong>Time On</strong>, <strong>Time Off</strong>, <strong>Duration</strong>, <strong>Analyst</strong>, <strong>Status</strong>.</li>
	<li>Status pills: <strong>Completed</strong>, <strong>In Progress</strong>, <strong>Not Started</strong>.</li>
	<li>Enter or update time on/off and analyst as your process requires, then save as offered on screen.</li>
	<li><strong>View Daily Equipment Usage</strong> — open a history/detail view; close with <strong>Close</strong>.</li>
	<li>History range may offer <strong>From</strong> / <strong>To</strong> dates.</li>
</ol>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-lightbulb-on-outline"></i></span>
	<p>
		Frequency slots (once to six times a day) and expected constant/range values are configured on the asset.
		If a unit is missing from the Daily Log page, edit the asset and confirm Requires Daily Log, Lab, and Value Types.
	</p>
</div>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-monitor-dashboard"></i></span>
	<p>
		Environmental Monitoring on the Monitoring page is a different workflow (template-driven lab conditions).
		Daily Log is the usage register for equipment flagged for daily logbook-style capture.
	</p>
</div>
