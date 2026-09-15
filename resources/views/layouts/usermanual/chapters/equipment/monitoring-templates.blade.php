<h2>Monitoring Template Engine</h2>
<p>
	From <strong>Equipment → Equipment Monitoring</strong>, click the <strong>Template Engine</strong> card.
	Heading on screen includes <strong>Template Engine</strong> with supporting text about variables and the formula engine.
	This is where administrators define what Environmental and Equipment monitoring runs look like.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/monitoring-templates.png',
	'caption' => 'Template Engine — list of templates with New Template and row actions.',
])

<h2>Template list</h2>
<ul>
	<li><strong>New Template</strong> — opens the create wizard (full page).</li>
	<li>Table columns typically: <strong>Actions</strong>, <strong>Template</strong>, <strong>Category</strong>, <strong>Document #</strong>, <strong>Version</strong>, <strong>Status</strong>, <strong>Fields</strong>, <strong>Formulas</strong>, <strong>Logs</strong>.</li>
</ul>

<h3>Row actions (exact titles)</h3>
<ul>
	<li><strong>Edit template</strong> — open the full edit wizard, or use the quick <strong>Edit Monitoring Template</strong> modal.</li>
	<li><strong>Clone template</strong> — confirm:
		<em>Clone this template with all fields, reading steps, formulas, and configured fields? Captured logs will not be copied.</em>
	</li>
	<li><strong>Clear captured logs</strong> — warning action that removes logs for that template (use with care).</li>
	<li><strong>Delete template</strong> — confirm deletion of the template and related logs.</li>
</ul>

<h3>Quick edit modal — Edit Monitoring Template</h3>
<ul>
	<li><strong>Template Name</strong></li>
	<li><strong>Document #</strong></li>
	<li><strong>Version</strong></li>
	<li><strong>Category</strong> — Environmental or Equipment</li>
	<li><strong>Status</strong></li>
	<li><strong>Template is active</strong> (when shown)</li>
	<li>Buttons: <strong>Cancel</strong>, <strong>Save Changes</strong></li>
</ul>

<h2>Create / Edit Template wizard</h2>
<p>
	Page titles: <strong>Create Monitoring Template</strong> or the edit equivalent.
	Steps (indicator on screen):
</p>
<ol>
	<li><strong>Basic Info</strong></li>
	<li><strong>Select Labs</strong></li>
	<li><strong>Select Items</strong></li>
	<li><strong>Reading Structure</strong></li>
	<li><strong>Configured Fields</strong></li>
</ol>
<p>Footer: <strong>Previous</strong> · Step X of 5 · <strong>Next</strong> · <strong>Cancel</strong> · on the last step <strong>Create Template</strong> (create) or save on edit.</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/equipment/monitoring-template-wizard.png',
	'caption' => 'Create Monitoring Template wizard — five steps from Basic Info to Configured Fields.',
])

<h3>Step 1 — Basic Info (Template Information)</h3>
<ul>
	<li><strong>Template Name *</strong></li>
	<li><strong>Document Control Number</strong></li>
	<li><strong>Version *</strong></li>
	<li><strong>Effective Date</strong></li>
	<li><strong>Status *</strong> — Draft / Active / Archived</li>
	<li><strong>Monitoring Type *</strong> — choose card <strong>Environmental</strong> or <strong>Equipment</strong> (sets the category used everywhere else).</li>
</ul>

<h3>Step 2 — Select Labs (Select Laboratories)</h3>
<p>
	Tick the laboratories that will use this template.
	Those labs appear as tabs on the Monitoring page under Assigned Laboratories.
</p>

<h3>Step 3 — Select Items</h3>
<ul>
	<li>For <strong>Environmental</strong> templates: select lab <strong>sections</strong> (areas) that will capture readings.</li>
	<li>For <strong>Equipment</strong> templates: select the <strong>equipment</strong> items that will appear under Equipment Due Today for those labs.</li>
</ul>

<h3>Step 4 — Reading Structure</h3>
<p>
	Define the ordered steps / variables of a reading.
	Typical actions in this step:
</p>
<ul>
	<li>Create or edit a reading step</li>
	<li>Remove a step</li>
	<li>Define a variable used by formulas</li>
</ul>
<p>These steps drive what the Execute / Save Reading screens ask for.</p>

<h3>Step 5 — Configured Fields</h3>
<p>
	Add the concrete fields operators fill in (text, number, choice, calculated).
	Use Add / Edit / Delete field modals as shown on screen.
	Formulas can reference variables from Reading Structure.
</p>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-alert-circle-outline"></i></span>
	<p>
		<strong>Clone</strong> is the safest way to invent a new version: it copies structure without copying historical logs.
		<strong>Clear captured logs</strong> and <strong>Delete</strong> are destructive — confirm carefully.
	</p>
</div>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-lightbulb-on-outline"></i></span>
	<p>
		After creating an Equipment-category template, go back to the <strong>Equipment Monitoring</strong> card,
		pick the lab, and use <strong>Execute</strong> / <strong>Save Log</strong> as described in the Equipment Monitoring chapter.
	</p>
</div>
