<h2>End-to-end Billing quotation path</h2>
<p>
	Use this chapter when commercial starts from <strong>Billing → Quotations</strong> rather than Process enquiry.
	It walks through creating the quote, building lines (with or without a pricelist), saving, approval, completion,
	and optionally turning the quote into a Test Request.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/tabs-in-preparation.png',
	'caption' => 'Quotations list — filter In Preparation while you build the quote.',
])

<h2>Step 1 — Add a new quotation</h2>
<ol>
	<li>Go to <strong>Billing → Quotations</strong> → <strong>In Preparation</strong> tab.</li>
	<li>Press <strong>+ Add Quotation</strong>.</li>
	<li>Fill the header:
		<ul>
			<li><strong>Client</strong> and <strong>Client contact</strong></li>
			<li><strong>Quotation type</strong> (e.g. External / Internal)</li>
			<li><strong>Lab section(s)</strong> that apply</li>
			<li><strong>Currency</strong>, validity dates, and reference fields as required</li>
		</ul>
	</li>
	<li>Save or open the new quote — you land on the line-item screen <strong>In Preparation</strong>.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/quotation-create.png',
	'caption' => 'New Quotation — client, contact, type, lab section(s), dates, and currency.',
])

<h2>Step 2 — Prepare line items</h2>
<p>
	This is where you define <strong>what</strong> is being tested and <strong>how much</strong> it costs.
	You can work <strong>without a pricelist</strong> (type prices yourself) or <strong>append lines from a customer pricelist</strong>
	(catalogue prices pulled in automatically). Both paths use the same line grid on the preparing quote.
</p>

<h3>2a — Add a line manually (+ Add line)</h3>
<ol>
	<li>On the preparing quote, press <strong>+ Add line</strong>.</li>
	<li>Choose a <strong>Sample type</strong> for the row.</li>
	<li>Open the <strong>Parameters</strong> picker (<strong>Quotation Parameters</strong> modal) and tick the tests that belong on this line.</li>
	<li>Enter <strong>Qty</strong> (number of samples), <strong>Unit price</strong>, and VAT if applicable.</li>
	<li>Repeat for each sample type / package you need on the quote.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/prep-lines.png',
	'caption' => 'Quote In Preparation — + Add line, parameters, qty, unit price, and Save lines.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/quotation-parameters-modal.png',
	'caption' => 'Quotation Parameters modal — tick which tests belong on this line.',
])

<h3>2b — Billing mode: per package (default) vs per parameter (per test)</h3>
<p>
	Each line has a <strong>Per parameter</strong> checkbox. This controls whether the customer is billed
	<strong>once per package</strong> or <strong>separately for each test</strong> on that line.
</p>

<h4>Per package — default (Per parameter <em>unchecked</em>)</h4>
<p>
	Use this when one unit price covers the whole bundle of tests on the line — the usual AmSpec package quote.
</p>
<ul>
	<li>Leave <strong>Per parameter</strong> <em>unchecked</em>.</li>
	<li>Tick all tests in the package via the Parameters modal.</li>
	<li>Enter one <strong>Unit price</strong> for the package. Help text reads:
		<em>“Independent quotation (package): enter unit price.”</em>
	</li>
	<li><strong>Math:</strong> line total = <strong>qty × unit price once</strong> — not multiplied by the number of tests in the package.</li>
</ul>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/prep-lines-2.png',
	'caption' => 'Per package mode — Per parameter off; tests listed on the line; one unit price for the bundle.',
])

<h4>Per parameter — per test billing (Per parameter <em>checked</em>)</h4>
<p>
	Use this when each analysis on the line should carry its own unit price — same idea as a <strong>per-test pricelist</strong>.
</p>
<ol>
	<li>On the line, tick <strong>Per parameter</strong>. The badge changes to e.g. <strong>PER PARAMETER · 2 TESTS</strong>.</li>
	<li>Each selected test row can have its own <strong>unit price</strong> and quantity.</li>
	<li><strong>Math:</strong> line total ≈ sum of (samples × unit price) for each parameter row on that line.</li>
	<li>To go back to a single package price, <strong>uncheck Per parameter</strong> — sibling rows merge into one package line.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/quotation-per-parameter-mode.png',
	'caption' => 'Per parameter mode — each test billed at its own unit price; badge shows test count.',
])

<div class="um-compare">
	<div class="um-compare__card">
		<h4>Per package (default)</h4>
		<p>One price for the whole sample package. Total = samples × unit price once. Tests define scope on the PDF; money is per bundle.</p>
	</div>
	<div class="um-compare__card">
		<h4>Per parameter (per test)</h4>
		<p>Each analysis has its own unit price. Total is the sum of per-test rows. Use when tests are priced individually.</p>
	</div>
</div>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-toggle-switch-outline"></i></span>
	<p>
		You can mix modes on one quote — some lines per package, others per parameter.
		When appending from a pricelist, new lines follow the <strong>list’s</strong> billing mode (package list vs per-test list).
		You can still toggle <strong>Per parameter</strong> on a line after append if your process allows manual edits.
	</p>
</div>

<h3>2c — Append lines from a customer pricelist (Commercial button)</h3>
<p>
	When the client is assigned to an active pricelist, you can pull catalogue lines instead of typing every price.
</p>

<h4>Before you start — pricelist must be eligible</h4>
<ul>
	<li>The quote header has a <strong>client</strong> selected.</li>
	<li>That client is <strong>assigned</strong> to at least one <strong>active</strong> pricelist
		(under <strong>Billing → Pricelists → Customer Assignment</strong>).</li>
	<li>The pricelist has <strong>applied</strong> selling prices (<strong>Apply Price Changes</strong> run after catalogue edits).</li>
</ul>

<h4>Append from pricelist</h4>
<ol>
	<li>On the preparing quote, press <strong>Commercial</strong> (list icon) — tooltip:
		<em>Append lines from customer pricelist</em>.</li>
	<li>In the <strong>Customer pricelist</strong> modal, pick an eligible list tile.</li>
	<li>Lines are <strong>appended</strong> — existing manual rows are kept.</li>
	<li>The quote <strong>binds</strong> to that list: list code, billing mode, and line count appear in the summary.</li>
	<li>Appended lines follow the list type:
		<ul>
			<li><strong>Package pricelist</strong> → one quotation line per package (qty × unit price once).</li>
			<li><strong>Per-test pricelist</strong> → one quotation line per catalogue test row (often with Per parameter on).</li>
		</ul>
	</li>
	<li>Review each line: adjust qty, toggle <strong>Per parameter</strong> if needed, or edit unit prices where allowed.</li>
	<li>To price freely again, use <strong>Detach pricelist (manual pricing)</strong> — this unbinds the list from the quote.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/prep-pricelist-chooser.png',
	'caption' => 'Commercial button — open Customer pricelist modal to append catalogue lines.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/prep-pricelist-no-eligible.png',
	'caption' => 'No eligible pricelist — assign the customer under Billing → Pricelists first, or add lines manually.',
])

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-book-open-outline"></i></span>
	<p>
		<strong>No eligible pricelist?</strong> The client is not linked to an active price book.
		Fix Customer Assignment, or continue with <strong>+ Add line</strong> and manual prices (sections 2a–2b above).
		See <strong>Pricelist in depth</strong> for building and maintaining price books.
	</p>
</div>

<h3>2d — Other ways to add lines</h3>
<ul>
	<li><strong>Import</strong> — load an AmSpec Excel/PDF template into this quote (see <strong>Import AmSpec template</strong>).</li>
	<li><strong>Manual entry</strong> — type unit prices on each line after choosing parameters (sections 2a–2b).</li>
</ul>

<p>
	For more detail on line math, VAT, and troubleshooting wrong totals, see <strong>Quote preparation &amp; math</strong>.
</p>

<h2>Step 3 — Save lines and review totals</h2>
<ol>
	<li>Press <strong>Save lines</strong> after every batch of edits.</li>
	<li>Check the footer totals (subtotal, VAT, grand total).</li>
	<li>Optional PDF columns (do not change the money):
		<ul>
			<li><strong>Show LOQ column</strong> — print LOQ on the customer PDF.</li>
			<li><strong>Show MU column</strong> — print measurement uncertainty % on the PDF.</li>
		</ul>
	</li>
	<li>Use <strong>Actions → Preview Quote</strong> or <strong>Process PDF</strong> to sanity-check the customer document before approval.</li>
</ol>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-calculator-variant-outline"></i></span>
	<p>
		Wrong total? Check: (1) per package vs per parameter on the line, (2) sample qty,
		(3) pricelist pending vs applied prices, (4) whether the quote is still bound to a list.
	</p>
</div>

<h2>Step 4 — Send for approval</h2>
<ol>
	<li>Press <strong>Move To workflow</strong> (top right — <em>not</em> inside the Actions menu).</li>
	<li>Choose notify channels (email and/or in-app bell — at least one).</li>
	<li>The quote moves to <strong>In Approval</strong>.</li>
</ol>
<p>Full approval steps (notify, approve, reject, complete) are in <strong>Approval &amp; notifications</strong> — Billing path.</p>

<h2>Step 5 — Approve or reject</h2>
<p>
	An approver opens the quote from <strong>Quotations → In Approval</strong>.
	<strong>Approve and send</strong> completes the quote and may email the PDF to the customer.
	<strong>Reject</strong> returns the quote to <strong>In Preparation</strong> for fixes — then repeat from Step 2.
</p>

<h2>Step 6 — Complete</h2>
<p>
	After approval, the quote appears under <strong>Complete</strong> with status <strong>Quote Complete</strong>.
	The customer may already have the PDF. You can re-send documents from <strong>Actions</strong> if needed.
</p>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-map-marker-path"></i></span>
	<p><strong>In Preparation</strong> = edit freely. <strong>In Approval</strong> = waiting on a decision. <strong>Complete</strong> = finished — change prices only if your process allows revision.</p>
</div>

<h2>Step 7 — Create enquiry from the quote (optional)</h2>
<p>
	After a quotation is <strong>Complete</strong>, you can spawn a formal <strong>Test Request Form (TRF)</strong> / enquiry
	so sample workflow can continue with tests and prices already agreed.
</p>

<h3>Who can do this?</h3>
<p>
	Users with permission to add quotations (typically commercial or reception staff).
	The action is available on the <strong>Complete</strong> list and on the quotation document view.
</p>

<h3>7a — Open the wizard</h3>
<ol>
	<li>Go to <strong>Billing → Quotations</strong> → <strong>Complete</strong> tab.</li>
	<li>Find the quote and press the orange <strong>beaker</strong> icon — tooltip: <em>Create enquiry from quotation</em>.</li>
	<li>Or open the quote PDF view and use the same action from the toolbar.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/quotations/quotation-complete-create-enquiry.png',
	'caption' => 'Quote Complete list — Create enquiry from quotation action (beaker icon).',
])

<h3>7b — Review (wizard)</h3>
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

<h3>7c — Choose sections</h3>
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

<h3>7d — Finish and create</h3>
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

<h3>7e — On the request view</h3>
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
	You can also price and approve from <strong>Process enquiry</strong> on an existing request
	(<strong>Build new</strong> → <strong>Send for Approval</strong>). See <strong>Process request (pricing)</strong>
	and <strong>Approval &amp; notifications</strong> for how the two paths compare.
</p>
