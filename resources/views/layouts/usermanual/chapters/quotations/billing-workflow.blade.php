<h2>End-to-end Billing quotation path</h2>
<p>Use this when commercial starts from the Quotations page.</p>

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
	<li><strong>Prepare lines</strong> — bind a customer pricelist if eligible, or work without a list (Add line / Import AmSpec).</li>
	<li><strong>Save lines</strong> — review totals, LOQ/MU columns if needed.</li>
	<li><strong>Move To workflow</strong> — notify approvers by email and/or bell; quote enters <strong>In Approval</strong>.</li>
	<li><strong>Approver</strong> — Approve and send, or Reject (back to preparation).</li>
	<li><strong>Complete</strong> — quote is finished; customer may already have the PDF.</li>
</ol>

<p>
	You can also reach the same quote stages from <strong>Process enquiry</strong> on a request
	(Build new → Send for Approval). See that chapter for the request-side door.
</p>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-map-marker-path"></i></span>
	<p>Preparation = edit freely. In Approval = waiting on a decision. Complete = done — revise carefully if prices must change.</p>
</div>
