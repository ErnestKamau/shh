<h2>End-to-end Billing quotation path</h2>
<p>
	Use this when commercial starts from the <strong>Quotations</strong> page rather than Process enquiry.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/quotation-create.png',
	'caption' => 'New Quotation — client, contact, type, lab section(s), dates, and currency.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/tabs-in-preparation.png',
	'caption' => 'Quotations list — stage filters: In Preparation, In Approval, Complete.',
])

<ol>
	<li><strong>Add Quotation</strong> — customer, contact, type, lab section(s), dates, currency.</li>
	<li><strong>Prepare lines</strong> — bind a customer pricelist if eligible, or work without a list (+ Add line / Import). See <strong>Quote preparation &amp; math</strong>.</li>
	<li><strong>Save lines</strong> — review totals, LOQ/MU columns if needed.</li>
	<li><strong>Move To workflow</strong> — notify approvers; quote enters <strong>In Approval</strong>.</li>
	<li><strong>Approver</strong> — Approve and send, or Reject (back to preparation). See <strong>Approval &amp; notifications</strong>.</li>
	<li><strong>Complete</strong> — quote is finished; customer may already have the PDF.</li>
	<li><strong>Create enquiry</strong> — turn the completed quote into a Test Request (below).</li>
</ol>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-map-marker-path"></i></span>
	<p>Preparation = edit freely. In Approval = waiting on a decision. Complete = done — revise carefully if prices must change.</p>
</div>

<h2>Create enquiry / request from a completed quotation</h2>
<p>
	After a quotation is <strong>Complete</strong>, you can spawn a formal <strong>Test Request Form (TRF)</strong> / enquiry
	so sample workflow can continue with tests and prices already agreed.
</p>

<h3>Who can do this?</h3>
<p>
	Users with permission to add quotations (typically commercial or reception staff).
	The action is available on the <strong>Complete</strong> list and on the quotation document view.
</p>

<h3>Step 1 — Open the wizard</h3>
<ol>
	<li>Go to <strong>Billing → Quotations</strong> → <strong>Complete</strong> tab.</li>
	<li>Find the quote and press the orange <strong>beaker</strong> icon — tooltip: <em>Create enquiry from quotation</em>.</li>
	<li>Or open the quote PDF view and use the same action from the toolbar.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/quotation-complete-create-enquiry.png',
	'caption' => 'Quote Complete list — Create enquiry from quotation action (beaker icon).',
])

<h3>Step 2 — Review (wizard)</h3>
<p>
	The <strong>Create enquiry from quotation</strong> wizard opens with three steps: <strong>Review</strong> → <strong>Choose sections</strong> → <strong>Finish</strong>.
</p>
<ul>
	<li><strong>Review</strong> — shows physical sample count, quote lines, and TRF forms to be created. Tests, sample types, and sample counts are <strong>locked from the quote</strong>; customer details apply automatically on the TRF.</li>
	<li>Confirm the quotation number, customer, and line summary, then press <strong>Continue</strong>.</li>
</ul>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/enquiry-from-quotation-review.png',
	'caption' => 'Wizard step 1 — Review quote lines, sample counts, and TRF forms locked from the quotation.',
])

<h3>Step 3 — Choose sections</h3>
<p>
	For each sample type on the quote, tick which TRF sections to complete now (you can fill the rest later on the request view):
</p>
<ul>
	<li><strong>Sample collection data</strong> — sampling method, transport, etc.</li>
	<li><strong>Test &amp; sample information</strong> — sample rows and test requirements (often pre-selected).</li>
	<li><strong>Miscellaneous</strong> — packaging, shipping, handling.</li>
	<li><strong>Submit &amp; sign</strong> — finalise the form.</li>
</ul>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/enquiry-from-quotation-choose-sections.png',
	'caption' => 'Wizard step 2 — Choose TRF sections to complete now for each sample type.',
])

<h3>Step 4 — Finish and create</h3>
<p>
	The <strong>Finish</strong> step summarises quotation, customer, origin (e.g. walk-in), and physical samples.
	If the quotation was already sent from Billing, a note explains that finishing creates the enquiry and TRF and marks the request as
	<strong>Quotation Sent</strong> without re-sending the PDF.
</p>
<ol>
	<li>Review the summary.</li>
	<li>Press <strong>Create enquiry &amp; TRF</strong>.</li>
	<li>Open the new request from Sample Workflow when redirected or from the board.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/enquiry-from-quotation-finish.png',
	'caption' => 'Wizard step 3 — Ready to create; Create enquiry & TRF button.',
])

<h3>Step 5 — On the request view</h3>
<p>
	The new request opens with status <strong>Quotation Sent</strong>.
	Documents include the <strong>TRF PDF</strong> and <strong>Quotation</strong> PDF.
	When the customer accepts, use <strong>Record quotation acceptance</strong> to move the workflow forward.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/request-quotation-sent.png',
	'caption' => 'Request view — Quotation Sent status, TRF and Quotation documents, Record quotation acceptance.',
])

<p>
	You can also reach the same quote stages from <strong>Process enquiry</strong> on an existing request
	(Build new → Send for Approval). That chapter covers pricing from the request side.
</p>
