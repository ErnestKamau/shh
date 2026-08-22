<h2>End-to-end Billing quotation path</h2>
<p>Use this when commercial starts from the Quotations page.</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/billing-workflow.png',
	'caption' => 'Quotations list — Add Quotation and stage filters.',
])

<ol>
	<li><strong>Add Quotation</strong> — customer and header details.</li>
	<li><strong>Prepare lines</strong> — prices from pricelist; save as you go.</li>
	<li><strong>Request for Approval</strong> — notify by email and/or bell.</li>
	<li><strong>Approver</strong> — on In Approval: Approve and send, or Reject.</li>
	<li><strong>Complete</strong> — quote is finished; customer may already have been sent the PDF.</li>
</ol>

<p>
	You can also reach the same quote stages from <strong>Process enquiry</strong> on a request
	(Build new → Send for Approval). See that chapter for the request-side door.
</p>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-map-marker-path"></i></span>
	<p>Preparation = edit freely. In Approval = waiting on a decision. Complete = done — revise carefully if prices must change.</p>
</div>
