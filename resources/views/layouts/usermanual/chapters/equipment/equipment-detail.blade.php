<h2>Equipment detail page</h2>
<p>
	From the Equipment List, click <strong>View</strong> on a row (or open the asset by ID).
	You see the full profile: header summary, actions, and a row of tabs for every related record.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/equipment-detail.png',
	'caption' => 'Equipment detail — header actions and main tabs.',
])

<h2>Header actions</h2>
<ul>
	<li><strong>Edit</strong> — opens the same five-step wizard to update the asset.</li>
	<li><strong>Back</strong> — returns to the previous screen (usually the list or maintenance programmes).</li>
</ul>
<p>
	If tabs overflow, use the left/right scroll buttons on the tab bar
	(<strong>Scroll tabs left</strong> / <strong>Scroll tabs right</strong>).
</p>

<h2>Main tabs (normal view)</h2>
<ol>
	<li><strong>Details</strong> — read-only summary of identity, assignment, schedules, and technical fields from the wizard.</li>
	<li><strong>Maintenance Log</strong> — per-asset service history (see Maintenance Log chapter). Button: <strong>Add Maintenance Log</strong>.</li>
	<li><strong>Calibration Log</strong> — calibrations and certificates (see Calibration Log). Button: <strong>Add Calibration Log</strong>.</li>
	<li><strong>Verification Log</strong> — intermediate checks (see Verification Log). Button: <strong>Add Verification Log</strong>.</li>
	<li><strong>Operators</strong> — people allowed to operate the unit. Button: <strong>Add Operator</strong>.</li>
	<li><strong>Attachments</strong> — files linked to the asset. Button: <strong>Add Attachment</strong>.</li>
	<li><strong>Notifications</strong> — chase reminders for calibration / maintenance / verification. Button: <strong>Add Notification</strong>.</li>
	<li><strong>Accessories</strong> — related accessories. Button: <strong>Add Accessory</strong> then <strong>Save Accessory</strong>.</li>
	<li><strong>Spare Parts</strong> — spare parts list. Button: <strong>Add Spare Part</strong> then <strong>Save Spare Part</strong>.</li>
	<li><strong>Daily Log Configuration</strong> — frequency and value types for daily logging (aligned with wizard Monitoring step).</li>
	<li><strong>Log Book</strong> — only when <strong>Equipment has logbook tracking</strong> was enabled. Configure columns and capture logbook entries.</li>
	<li><strong>Asset Depreciation</strong> — depreciation configuration and book-value information for this asset.</li>
</ol>

<h2>Tabs when opened from Equipment Maintenance</h2>
<p>
	If you open an asset from a maintenance programme (URL includes a maintenance context),
	extra programme tabs may appear instead of (or in addition to) some of the above:
</p>
<ul>
	<li><strong>Annual (TSU/F/06)</strong> — annual programme records for this asset. <strong>Add Annual Record</strong> / <strong>Save Record</strong>.</li>
	<li><strong>Preventive (TSU/F/05)</strong> — preventive programme rows. <strong>Add Preventive Record</strong>.</li>
	<li><strong>Maintenance Register</strong> — register entries with costs/providers. <strong>Add Register Entry</strong> / <strong>Save Entry</strong>.</li>
</ul>
<p>Programme-level calendars are covered in the Equipment Maintenance chapter.</p>

<h2>Typical workflow on a profile</h2>
<ol>
	<li>Confirm <strong>Details</strong> (status, department, lab, intervals).</li>
	<li>After a service visit, add a <strong>Calibration</strong> or <strong>Maintenance</strong> log with certificate if available.</li>
	<li>For intermediate checks, add a <strong>Verification</strong> log.</li>
	<li>Keep <strong>Operators</strong> and <strong>Attachments</strong> up to date.</li>
	<li>Set <strong>Notifications</strong> so the system can chase due dates.</li>
</ol>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-lightbulb-on-outline"></i></span>
	<p>
		Deep field-by-field help for each log type is in the next chapters.
		Use this page as the map of where everything lives on one asset.
	</p>
</div>
