<h2>What is a Request for Quotation?</h2>
<p>
	A <strong>Request for Quotation (RFQ)</strong> is how <strong>Procurement</strong> asks suppliers to price the items from an approved Purchase Request.
	You invite one or more suppliers, send them the RFQ by email, record their quotes, award the winning supplier(s), get internal approval, and then create a Purchase Order.
</p>
<p>
	Most RFQs are created automatically when Procurement clicks <strong>Send to Procurement (create RFQ)</strong> on an approved Purchase Request.
	You can also open existing RFQs from <strong>Inventory Management → Request to Order → Request for Quotation</strong>.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rfq-list.png',
	'caption' => 'RFQ record — General tab after Procurement creates the RFQ from an approved PR.',
])

<h2>Who does what</h2>
<table class="table table-bordered table-sm">
	<thead>
		<tr>
			<th>Role</th>
			<th>Typical tasks on an RFQ</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><strong>Procurement</strong> (Inventory Procurement Group)</td>
			<td>Add suppliers, write email body, send RFQs, enter/accept quotes, award suppliers, send for approval, create Purchase Order</td>
		</tr>
		<tr>
			<td><strong>Approver</strong> (role set under Approval Configuration)</td>
			<td>Review awarded RFQ and Approve, Reject, or Return</td>
		</tr>
		<tr>
			<td><strong>Requester</strong> (department staff)</td>
			<td>Usually does not edit the RFQ; may view Requisition Flow to track progress</td>
		</tr>
		<tr>
			<td><strong>Administrator</strong></td>
			<td>Sets up Approval Configuration on the RFQ list (same pattern as Purchase Request)</td>
		</tr>
	</tbody>
</table>

<h2>The RFQ list page</h2>
<p>
	Like Purchase Request, the RFQ list has three tabs:
</p>
<ul>
	<li><strong>Requests</strong> — active RFQs (In Preparation through Approval Complete).</li>
	<li><strong>Completed</strong> — finished RFQs (for example after a Purchase Order was created and the RFQ was marked complete).</li>
	<li><strong>Approval Configuration</strong> — define who must approve RFQs before a Purchase Order can be raised.</li>
</ul>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/pr-approval-config.png',
	'caption' => 'Approval Configuration tab — same layout on the RFQ list (Title, Users, + Add, Edit).',
])

<h3>Approval Configuration for RFQs</h3>
<p>
	Works the same way as on Purchase Request (see the Purchase Request chapter for the full walkthrough):
</p>
<ul>
	<li><strong>Title</strong> — label for the approval step (for example <em>Finance</em>).</li>
	<li><strong>Users</strong> — everyone in the linked role who can approve.</li>
	<li><strong>+ Add</strong> — open <strong>Add New Approval</strong>, enter a title, and pick a <strong>Select Role</strong>.</li>
	<li><strong>Edit (pencil)</strong> — change title, role, or level on an existing step.</li>
</ul>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/pr-approval-config-add.png',
	'caption' => 'Add New Approval modal — enter title and select role (identical on RFQ list).',
])
<p>
	Set this up before RFQs start going for approval so the right manager or finance contact is notified.
</p>

<h2>RFQ record — tabs overview</h2>
<p>
	Each RFQ has <strong>eight tabs</strong> (two more than a Purchase Request):
</p>
<ol>
	<li><strong>General</strong> — header details, dates, currency, and request value.</li>
	<li><strong>Items</strong> — what to buy (usually copied from the PR).</li>
	<li><strong>Suppliers</strong> — who to invite and whether the RFQ was sent / quote received.</li>
	<li><strong>Quotes</strong> — compare prices, award, edit, or remove quotes.</li>
	<li><strong>Approvals</strong> — internal sign-off after awarding.</li>
	<li><strong>Notes</strong> — internal comments.</li>
	<li><strong>Attachments</strong> — supporting files (for example supplier quote PDFs).</li>
	<li><strong>Requisition Flow</strong> — visual chain from PR → RFQ → PO → Goods Receipt.</li>
</ol>
<p>
	Top-right action buttons change with status. Common buttons:
	<strong>Add Email Body</strong> / <strong>Preview Email Body</strong>,
	<strong>Send Out RFQS</strong>,
	<strong>Get Approval</strong>,
	<strong>Create Purchase Order</strong>,
	<strong>Mark as Complete</strong>,
	and <strong>Save</strong>.
</p>

<h2>General tab</h2>
<p>
	Header information for the RFQ. Most fields are copied from the source Purchase Request.
	<strong>Procurement</strong> reviews and completes RFQ-specific fields before sending to suppliers.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rfq-create-general.png',
	'caption' => 'General tab — description, cost centre, department, and priority (In Preparation).',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rfq-create-general-fields.png',
	'caption' => 'General tab — currency, submission deadline, valid until, and request value.',
])

<h3>Left column</h3>
<ul>
	<li><strong>Description/Purpose</strong> — carried over from the PR; explains why the purchase is needed.</li>
	<li><strong>Company Unit</strong> — your location (for example <em>Dubai Lab</em>). Read-only.</li>
	<li><strong>Department</strong> — requesting department. Read-only.</li>
	<li><strong>Cost Center*</strong> — budget centre(s) to charge. Required.</li>
	<li><strong>Nature of Purchase*</strong> — spend classification (for example Normal, Capex).</li>
	<li><strong>Currency*</strong> — default currency for the RFQ (required on RFQ; used when comparing quotes).</li>
	<li><strong>Submission Deadline*</strong> — date and time by which suppliers must submit quotes.</li>
	<li><strong>Valid Until*</strong> — how long awarded prices remain valid.</li>
	<li><strong>Request Value</strong> — calculated total from item lines (read-only on RFQ).</li>
</ul>

<h3>Right column</h3>
<ul>
	<li><strong>Requested By</strong> — original requester from the PR.</li>
	<li><strong>Created On / Created By / Updated On</strong> — audit trail.</li>
	<li><strong>Priority*</strong> — urgency (for example Normal).</li>
	<li><strong>Status</strong> — current RFQ stage (see status table below).</li>
</ul>

<p><strong>Role:</strong> Procurement reviews and updates dates and currency before sending the RFQ.</p>

<h2>Items tab</h2>
<p>
	Lists every line to be quoted. Items are normally copied from the approved PR.
	While status is <em>In Preparation</em>, Procurement can still add or remove lines if needed.
</p>
<p>
	The Items tab uses the same grid layout as Purchase Request (item, brand, quantity, delivery date, shipping mode) with one extra <strong>Shipping Mode</strong> column on RFQs.
</p>

<h3>Columns</h3>
<ul>
	<li><strong>#</strong> — line number.</li>
	<li><strong>Item</strong> — catalogue item name and code.</li>
	<li><strong>Brand</strong> — preferred brand from the PR.</li>
	<li><strong>Description/Location</strong> — extra detail or delivery location notes.</li>
	<li><strong>UoM</strong> — unit of measure.</li>
	<li><strong>Open Quantity</strong> — current stock reference (read-only).</li>
	<li><strong>Requested Quantity</strong> — how many units to quote.</li>
	<li><strong>Delivery Date</strong> — when goods are needed.</li>
	<li><strong>Shipping Mode</strong> — how goods should be shipped (for example air, road).</li>
</ul>
<p>
	Use <strong>+ Add</strong> and <strong>Remove</strong> while the RFQ is still in preparation.
	After quotes are sent, lines are locked.
</p>
<p><strong>Role:</strong> Procurement (and sometimes the original requester if the RFQ is returned for correction).</p>

<h2>Suppliers tab</h2>
<p>
	Choose which suppliers receive this RFQ.
	You need at least one supplier and an email body before you can click <strong>Send Out RFQS</strong>.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rfq-suppliers-empty.png',
	'caption' => 'Suppliers tab — no suppliers added yet.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rfq-suppliers-added.png',
	'caption' => 'Suppliers tab — supplier added, ready for email body and send.',
])

<h3>Adding suppliers</h3>
<ul>
	<li><strong>+ Supplier</strong> — search and pick from your supplier catalogue (only visible to Procurement).</li>
	<li><strong>Remove</strong> — delete ticked supplier rows (before or after send, while status allows).</li>
</ul>

<h3>Supplier table columns</h3>
<ul>
	<li><strong>Name</strong> — supplier company name.</li>
	<li><strong>Rating</strong> — supplier score from past performance (star rating helps compare vendors).</li>
	<li><strong>Email</strong> — where the RFQ email is sent. Keep supplier records up to date under <strong>Suppliers</strong> in Inventory.</li>
	<li><strong>Phone</strong> — contact number.</li>
	<li><strong>RFQ Sent</strong> — green tick when the email went out; red X before send.</li>
	<li><strong>Quote Received</strong> — green tick when a quote is recorded; red X until then. Click <strong>Enter Price</strong> or <strong>Accept Quote</strong> to open the quote entry modal.</li>
</ul>
<p><strong>Role:</strong> Procurement only.</p>

<h2>Email body — before sending</h2>
<p>
	Before <strong>Send Out RFQS</strong> appears, you must add the email text suppliers will receive.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rfq-email-body.png',
	'caption' => 'Configure Email Body — message and item table sent to suppliers.',
])

<ol>
	<li>Click <strong>Add Email Body</strong> (or <strong>Preview Email Body</strong> if already saved).</li>
	<li>Edit the message in <strong>Configure Email Body</strong>. A default template is provided; customise as needed.</li>
	<li>Review the <strong>RFQ Items</strong> table at the bottom — this is what suppliers see (code, description, quantity, UoM).</li>
	<li>Click <strong>Update</strong> to save the email body.</li>
</ol>
<p>
	After the body is saved, <strong>Preview Email Body</strong> lets you check it any time.
	<strong>Send Out RFQS</strong> only shows when suppliers are added, the email body exists, and status is not yet Awarded or Approval Complete.
</p>
<p><strong>Role:</strong> Procurement only.</p>

<h2>Send Out RFQS</h2>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rfq-sent-out.png',
	'caption' => 'After sending — status changes to RFQs sent out (General tab).',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rfq-suppliers-sent.png',
	'caption' => 'Suppliers tab — RFQ Sent shows a green tick for each supplier emailed.',
])

<ol>
	<li>Confirm General dates, Items, Suppliers, and email body are correct.</li>
	<li>Click <strong>Send Out RFQS</strong>.</li>
	<li>Status becomes <strong>RFQs sent out</strong>.</li>
	<li>On the Suppliers tab, <strong>RFQ Sent</strong> shows a green tick for each supplier.</li>
</ol>
<p>
	Suppliers receive the RFQ by email and can reply with their prices.
	You can click <strong>Send Out RFQS</strong> again later to resend or include newly added suppliers (while status still allows).
</p>
<p><strong>Role:</strong> Procurement only.</p>

<h2>Recording a supplier quote</h2>
<p>
	When a supplier responds (by email, phone, or portal), Procurement records the quote in the system.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rfq-accept-quote-modal.png',
	'caption' => 'Supplier Quote modal — enter price per item before RFQ was sent (Enter Price).',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rfq-accept-quote-filled.png',
	'caption' => 'Supplier Quote modal — currency, amount, VAT, and attached quote PDF.',
])

<p>On the <strong>Suppliers</strong> tab, click <strong>Enter Price</strong> (before send) or <strong>Accept Quote</strong> (after send) on the supplier row.</p>
<p>The <strong>Supplier Quote</strong> modal opens:</p>
<ul>
	<li>Tick each <strong>item</strong> the supplier quoted for.</li>
	<li><strong>Quantity</strong> — shown from the RFQ line (read-only).</li>
	<li><strong>Currency</strong> — select the quote currency (for example EUR, AED).</li>
	<li><strong>Amount</strong> — unit or line price from the supplier.</li>
	<li><strong>VAT(?)</strong> — VAT percentage, and whether the amount is <strong>Exc</strong> (exclusive) or <strong>Inc</strong> (inclusive) of VAT.</li>
	<li><strong>Attach Supplier Quote</strong> — upload the supplier’s PDF or document (recommended for audit).</li>
	<li>Click <strong>Accept Quote</strong> to save.</li>
</ul>
<p>
	After a quote is saved, status moves to <strong>Receiving Quotes</strong> and you see a success message.
	The supplier’s <strong>Quote Received</strong> column shows a green tick.
	An attachment may appear on the <strong>Attachments</strong> tab (for example <em>Quote from [Supplier Name]</em>).
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rfq-receiving-quotes.png',
	'caption' => 'Receiving Quotes — quote saved and attachment added.',
])

<p><strong>Role:</strong> Procurement only.</p>

<h2>Quotes tab</h2>
<p>
	All recorded quotes appear here for comparison.
	Lowest-price lines may be highlighted to help you choose.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rfq-quotes-tab.png',
	'caption' => 'Quotes tab — compare suppliers, award, edit, or remove quotes.',
])

<h3>Toolbar</h3>
<ul>
	<li><strong>Download CSV</strong> — export the quote table for offline comparison.</li>
	<li><strong>Send Reminders</strong> — email suppliers who have not yet quoted (after RFQ was sent).</li>
</ul>

<h3>Quote table columns</h3>
<ul>
	<li><strong>#</strong> — row number.</li>
	<li><strong>Supplier</strong> — vendor name (checkbox for multi-award).</li>
	<li><strong>Item</strong> — product quoted.</li>
	<li><strong>Brand</strong> — brand offered (or Non-Specific).</li>
	<li><strong>Price</strong> — currency, amount, VAT % and Exc/Inc.</li>
	<li><strong>Awarded?</strong> — actions or award timestamp.</li>
</ul>

<h3>Actions on each quote row</h3>
<ul>
	<li><strong>Award Supplier</strong> — select this quote as the winner (opens reason modal).</li>
	<li><strong>Edit Quote</strong> — change amount with a documented reason.</li>
	<li><strong>Remove Quote</strong> — delete an incorrect entry.</li>
	<li><strong>Undo award</strong> (icon after award) — reverse an award before approval if you picked the wrong supplier.</li>
</ul>
<p>
	For multiple items you can tick several rows and use <strong>Award Suppliers</strong> (bulk award) when shown.
</p>
<p><strong>Role:</strong> Procurement awards; approvers review the outcome later.</p>

<h2>Awarding a supplier</h2>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rfq-award-supplier-modal.png',
	'caption' => 'Award Supplier — enter a reason for choosing this quote.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rfq-awarded.png',
	'caption' => 'Awarded — Quotes tab with winning supplier highlighted and award timestamp.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rfq-awarded-quotes.png',
	'caption' => 'Quotes tab — awarded row highlighted with timestamp.',
])

<ol>
	<li>On the <strong>Quotes</strong> tab, click <strong>Award Supplier</strong> on the winning row.</li>
	<li>In the <strong>Award Supplier</strong> modal, enter a <strong>Reason</strong> (for example lowest price, preferred vendor, fastest delivery).</li>
	<li>Click <strong>Award</strong>.</li>
	<li>Status becomes <strong>Awarded</strong>.</li>
	<li>The awarded row shows a green check and the award date/time.</li>
</ol>
<p>
	You must award at least one quote before you can send the RFQ for internal approval or create a Purchase Order.
</p>
<p><strong>Role:</strong> Procurement only.</p>

<h2>Approvals tab</h2>
<p>
	After awarding, Procurement sends the RFQ for internal sign-off.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rfq-approvals-awaiting.png',
	'caption' => 'Approvals tab — Awaiting Approval with Approve, Reject, Return actions.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rfq-confirm-approval.png',
	'caption' => 'Confirm Approval — approver confirms before signing off.',
])

<h3>Sending for approval</h3>
<p><strong>Who:</strong> Procurement.</p>
<ol>
	<li>When status is <strong>Awarded</strong> and at least one quote is awarded, click <strong>Get Approval</strong>.</li>
	<li>Status becomes <strong>Awaiting Approval</strong>.</li>
	<li>Configured approvers are notified.</li>
</ol>

<h3>Approver actions</h3>
<p><strong>Who:</strong> User in the role configured under RFQ Approval Configuration (for example Inventory Manager Group).</p>
<ul>
	<li>Review <strong>General</strong>, <strong>Items</strong>, <strong>Quotes</strong> (who was awarded and at what price), and <strong>Attachments</strong>.</li>
	<li>On <strong>Approvals</strong>, click <strong>Approve</strong>, <strong>Reject</strong>, or <strong>Return</strong>.</li>
	<li><strong>Approve</strong> opens <strong>Confirm Approval</strong> — click <strong>Yes, Approve</strong> to confirm.</li>
	<li><strong>Return</strong> sends the RFQ back to Procurement for changes.</li>
	<li><strong>Send Reminder</strong> nudges pending approvers.</li>
</ul>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rfq-approval-complete.png',
	'caption' => 'Approval Complete — Requisition Flow with Create Purchase Order and Mark as Complete.',
])

<p>
	When all steps are approved, status is <strong>Approval Complete</strong>.
	<strong>Create Purchase Order</strong> and <strong>Mark as Complete</strong> appear for Procurement.
</p>

<h2>Notes tab</h2>
<p>
	Internal comments visible to staff with access to this RFQ.
</p>
<ul>
	<li><strong>+ Note</strong> — add a note with a note type.</li>
	<li><strong>Remove</strong> — delete selected notes.</li>
	<li>Columns: <strong>Note Type</strong>, <strong>Created By</strong>, <strong>Created On</strong>.</li>
</ul>
<p>
	Use Notes for handover comments (for example “award based on framework agreement”) or follow-up after a Return from an approver.
</p>
<p><strong>Role:</strong> Procurement and approvers; any user with access can add notes.</p>

<h2>Attachments tab</h2>
<p>
	Store files linked to this RFQ — supplier quote PDFs, specifications, or email printouts.
</p>
<ul>
	<li><strong>+ Attachment</strong> — upload a file with title and type.</li>
	<li><strong>Remove</strong> — delete selected files.</li>
	<li>Columns: <strong>File</strong> (download link), <strong>Title</strong>, <strong>Type</strong>, <strong>Created By</strong>, <strong>Created On</strong>, <strong>Description</strong>.</li>
</ul>
<p>
	When you attach a file in the <strong>Supplier Quote</strong> modal, it may also appear here automatically (for example <em>Quote from Francis Hoover</em>).
</p>
<p><strong>Role:</strong> Procurement typically uploads; approvers read before signing off.</p>

<h2>Requisition Flow tab</h2>
<p>
	Shows the full buying chain for this request.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rfq-requisition-flow.png',
	'caption' => 'Requisition Flow — PR and RFQ complete; PO and Goods Receipt not yet created.',
])

<ul>
	<li><strong>Purchase Request</strong> — source PR with status (for example Approval Complete). Click <strong>View</strong> or <strong>Source</strong> to open it.</li>
	<li><strong>Request for Quotation</strong> — this RFQ, linked back to the PR.</li>
	<li><strong>Purchase Orders</strong> — <em>Not yet at this stage</em> until you create a PO.</li>
	<li><strong>Goods Receipt</strong> — <em>Not yet at this stage</em> until store receives goods.</li>
</ul>
<p>
	Use <strong>Generate PDF</strong> to export the flow document when needed.
</p>

<h2>Create Purchase Order</h2>
<p>
	After <strong>Approval Complete</strong>, Procurement converts the awarded RFQ into a Purchase Order.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rfq-create-po-modal.png',
	'caption' => 'Create Purchase Order Confirmation — optional split across suppliers.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rfq-po-created.png',
	'caption' => 'Purchase Order created from the RFQ — PO opens in In Preparation with success message.',
])

<ol>
	<li>Click <strong>Create Purchase Order</strong>.</li>
	<li>In <strong>Create Purchase Order Confirmation</strong>, confirm you want to proceed.</li>
	<li>Optional: tick <strong>PO contain items that you want to split between 2 or more suppliers?</strong> if different lines go to different vendors — then pick items and suppliers.</li>
	<li>Click <strong>Yes, Proceed</strong>.</li>
	<li>A new Purchase Order is created (for example <em>PO20260005</em>) in <em>In Preparation</em> status.</li>
	<li>Continue in the <strong>Purchase Order</strong> chapter — approve and send the order to the supplier.</li>
</ol>
<p>
	You can click <strong>Mark as Complete</strong> on the RFQ after the PO is created if your process closes the RFQ at that point.
</p>
<p><strong>Role:</strong> Procurement only.</p>

<h2>RFQ status summary</h2>
<table class="table table-bordered table-sm">
	<thead>
		<tr>
			<th>Status</th>
			<th>Meaning</th>
			<th>Who acts next</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><em>In Preparation</em></td>
			<td>RFQ created; suppliers and email body being set up</td>
			<td>Procurement — add suppliers, email body, then Send Out RFQS</td>
		</tr>
		<tr>
			<td><em>RFQs sent out</em></td>
			<td>Emails sent to suppliers</td>
			<td>Procurement — record quotes when suppliers respond</td>
		</tr>
		<tr>
			<td><em>Receiving Quotes</em></td>
			<td>At least one quote entered</td>
			<td>Procurement — enter more quotes, then Award Supplier</td>
		</tr>
		<tr>
			<td><em>Awarded</em></td>
			<td>Winning supplier(s) chosen</td>
			<td>Procurement — Get Approval</td>
		</tr>
		<tr>
			<td><em>Awaiting Approval</em></td>
			<td>With internal approver(s)</td>
			<td>Approver — Approve, Reject, or Return</td>
		</tr>
		<tr>
			<td><em>Partially Approved</em></td>
			<td>Some approval steps done</td>
			<td>Next approver in sequence</td>
		</tr>
		<tr>
			<td><em>Approval Complete</em></td>
			<td>Fully approved</td>
			<td>Procurement — Create Purchase Order</td>
		</tr>
		<tr>
			<td><em>Rejected</em></td>
			<td>Approver declined the RFQ</td>
			<td>Procurement — review and possibly restart from PR</td>
		</tr>
	</tbody>
</table>

<h2>End-to-end RFQ process (quick reference)</h2>
<ol>
	<li><strong>PR approved</strong> → Procurement creates RFQ from Purchase Request.</li>
	<li><strong>General + Items</strong> → review copied data; set currency, submission deadline, valid until.</li>
	<li><strong>Suppliers</strong> → add vendor(s).</li>
	<li><strong>Email body</strong> → Add Email Body, customise message, Update.</li>
	<li><strong>Send Out RFQS</strong> → suppliers emailed.</li>
	<li><strong>Accept Quote</strong> → enter prices and attach supplier documents.</li>
	<li><strong>Quotes tab</strong> → compare; <strong>Award Supplier</strong> with reason.</li>
	<li><strong>Get Approval</strong> → approver signs off.</li>
	<li><strong>Create Purchase Order</strong> → PO raised from awarded quote.</li>
</ol>

<h2>Tips for Procurement</h2>
<ul>
	<li>Invite at least two suppliers when possible so the Quotes tab gives a real comparison.</li>
	<li>Always attach the supplier’s written quote PDF when accepting a quote.</li>
	<li>Enter a clear <strong>Reason</strong> when awarding — approvers and auditors will read it.</li>
	<li>Use <strong>Send Reminders</strong> if quotes are slow before the submission deadline.</li>
	<li>Check supplier <strong>Email</strong> and <strong>Rating</strong> on the Suppliers tab before sending.</li>
	<li>Track the full chain on <strong>Requisition Flow</strong> so requesters can see progress without asking.</li>
</ul>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-email-send"></i></span>
	<p>
		After the Purchase Order is created, continue in the <strong>Purchase Order</strong> chapter.
		The RFQ remains visible on Requisition Flow for traceability back to the original Purchase Request.
	</p>
</div>
