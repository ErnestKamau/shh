<h2>Equipment Dashboard</h2>
<p>
	Open <strong>Equipment → Equipment Dashboard</strong> (also the first screen after you enter the module).
	Use it for a quick health check of the whole fleet before you dive into individual assets.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/dashboard.png',
	'caption' => 'Equipment Dashboard — KPI cards, charts, and quick actions.',
])

<h2>KPI cards</h2>
<p>Each card summarises a count. Typical cards include:</p>
<ul>
	<li><strong>Total Equipment</strong> — all registered assets (excluding pure disposal-only views).</li>
	<li><strong>Active</strong> — assets marked active / in normal use.</li>
	<li><strong>Calibration Attention</strong> / <strong>Calibration Overdue</strong> — units inside the notification window or past due for calibration.</li>
	<li><strong>Maintenance Attention</strong> / <strong>Maintenance Overdue</strong> — same idea for maintenance intervals.</li>
	<li><strong>Disposed</strong> — assets that have gone through disposal.</li>
</ul>

<h2>Charts and panels</h2>
<ul>
	<li><strong>Status Distribution</strong> — how many assets sit in each operational status (for example Active, Obsolete, Out Of Service).</li>
	<li><strong>Maintenance &amp; Calibration Health</strong> — overview of schedule health across the register.</li>
	<li><strong>Purchase Trend (Last 6 Months)</strong> — when assets were purchased recently.</li>
	<li><strong>Critical Equipment</strong> — units that need attention now. If none need attention you may see a healthy-state message such as <strong>All Equipment Healthy</strong>.</li>
</ul>

<h2>Buttons and quick actions</h2>
<ul>
	<li><strong>Open Equipment List</strong> — jumps to the full register.</li>
	<li><strong>Add Equipment</strong> — opens the create wizard (same flow as on the list).</li>
	<li><strong>Import Equipment</strong> — opens bulk import.</li>
	<li><strong>Reports</strong> — opens equipment reports.</li>
	<li><strong>Asset Types</strong> — opens asset type master data.</li>
</ul>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-lightbulb-on-outline"></i></span>
	<p>
		Treat overdue calibration or maintenance cards as your daily cue:
		open the list or the asset profile and record the log (see the Calibration Log and Maintenance Log chapters).
	</p>
</div>
