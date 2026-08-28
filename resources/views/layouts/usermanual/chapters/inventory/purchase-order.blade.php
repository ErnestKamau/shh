<h2>What is a Purchase Order?</h2>
<p>
	A <strong>Purchase Order (PO)</strong> is the formal order sent to a supplier after an RFQ is approved and quotes are awarded.
	It confirms what you are buying, from whom, at what price, and when you need delivery.
	Store staff then receive goods against the PO using <strong>Create Goods Receipt</strong>.
</p>
<p>
	Most POs are created from an approved RFQ using <strong>Create Purchase Order</strong>.
	Open existing orders under <strong>Inventory Management → Request to Order → Purchase Orders</strong>.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/po-create-general.png',
	'caption' => 'Purchase Order — General tab in In Preparation status.',
])

<h2>Who does what</h2>
<table class="table table-bordered table-sm">
	<thead>
		<tr>
			<th>Role</th>
			<th>Typical PO tasks</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><strong>Procurement</strong> (Inventory Procurement Group)</td>
			<td>Review PO after creation, send for approval, send PO to supplier, mark as complete</td>
		</tr>
		<tr>
			<td><strong>Approver</strong> (role under Approval Configuration)</td>
			<td>Approve, Reject, or Return the PO before it is sent</td>
		</tr>
		<tr>
			<td><strong>Store Manager</strong> (Inventory Store Manager Group)</td>
			<td>Create Goods Receipt (partial or full), create return notes when needed</td>
		</tr>
		<tr>
			<td><strong>Requester</strong></td>
			<td>May view progress on Requisition Flow; notified when goods arrive at store</td>
		</tr>
		<tr>
			<td><strong>Administrator</strong></td>
			<td>Sets up PO Approval Configuration on the Purchase Orders list</td>
		</tr>
	</tbody>
</table>

<h2>The Purchase Orders list</h2>
<p>
	The list has three tabs, same pattern as Purchase Request and RFQ:
</p>
<ul>
	<li><strong>Requests</strong> — active POs (In Preparation through Purchase Order Sent).</li>
	<li><strong>Completed</strong> — closed POs.</li>
	<li><strong>Approval Configuration</strong> — who must approve POs before they are sent.</li>
</ul>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/po-approval-config.png',
	'caption' => 'PO Approval Configuration — Finance approvers assigned by role.',
])

<h3>Approval Configuration for Purchase Orders</h3>
<ul>
	<li><strong>Title</strong> — approval step name (for example <em>Finance</em>).</li>
	<li><strong>Users</strong> — staff in the linked role who can approve POs.</li>
	<li><strong>+ (top right)</strong> — add a new approval step (title + role).</li>
	<li><strong>Edit (pencil)</strong> — change an existing step.</li>
</ul>
<p>
	Configure this before POs go for approval so the right manager or finance contact is assigned automatically.
</p>

<h2>PO record — tabs overview</h2>
<p>Each Purchase Order has six tabs:</p>
<ol>
	<li><strong>General</strong> — header, supplier, dates, and value.</li>
	<li><strong>Items</strong> — order lines with quantities, prices, and receiving progress.</li>
	<li><strong>Approvals</strong> — internal sign-off before sending to supplier.</li>
	<li><strong>Notes</strong> — internal comments.</li>
	<li><strong>Attachments</strong> — supporting documents.</li>
	<li><strong>Requisition Flow</strong> — chain from PR → RFQ → PO → Goods Receipt, plus PDF access.</li>
</ol>

<h2>General tab</h2>
<p>
	Header and supplier details for the order.
	When the PO is first created from an RFQ, most fields are pre-filled from the awarded quote.
</p>

<h3>Left column</h3>
<ul>
	<li><strong>Description/Purpose</strong> — summary of what is being ordered (often item names and quantities).</li>
	<li><strong>Company Unit</strong> — your location (for example <em>Dubai Lab</em>). Read-only.</li>
	<li><strong>Department</strong> — requesting department.</li>
	<li><strong>Cost Center*</strong> — budget centre to charge. Required.</li>
	<li><strong>Supplier</strong> — vendor name and logo (from the awarded RFQ quote). Read-only.</li>
	<li><strong>Supplier Email</strong> — where the PO will be sent.</li>
	<li><strong>Supplier Phone</strong> — supplier contact number.</li>
	<li><strong>Currency*</strong> — order currency (for example EUR).</li>
	<li><strong>Delivery Date</strong> — required delivery date for the order.</li>
	<li><strong>Valid Until</strong> — PO validity end date (if set).</li>
	<li><strong>Request Value</strong> — total net value of the order (editable by Procurement while in preparation).</li>
</ul>

<h3>Right column</h3>
<ul>
	<li><strong>Requested By</strong> — original requester from the PR.</li>
	<li><strong>Created On / Created By / Updated On</strong> — audit trail.</li>
	<li><strong>Priority*</strong> — urgency level.</li>
	<li><strong>Status</strong> — current PO stage (see status table below).</li>
</ul>

<p><strong>Role:</strong> Procurement reviews and adjusts delivery date and value if needed before sending for approval.</p>

<h2>Items tab</h2>
<p>
	Every line on the purchase order.
	Lines are copied from the awarded RFQ quote, including price and supplier.
</p>

<h3>Columns</h3>
<ul>
	<li><strong>#</strong> — line number.</li>
	<li><strong>Item</strong> — catalogue item.</li>
	<li><strong>Brand</strong> — brand from the quote.</li>
	<li><strong>Description/Location</strong> — delivery or usage notes.</li>
	<li><strong>UoM</strong> — unit of measure.</li>
	<li><strong>Requested Quantity</strong> — quantity ordered on this PO.</li>
	<li><strong>Pending Quantity</strong> — how much is still waiting to be received (read-only). Decreases as Goods Receipts are created.</li>
	<li><strong>Received Quantity</strong> — how much has already been received via Goods Receipt (read-only).</li>
	<li><strong>Returned Qty</strong> — quantity returned to supplier via Goods Return, if any.</li>
	<li><strong>Shipping Mode</strong> — delivery method.</li>
	<li><strong>Store</strong> — destination store for received goods.</li>
	<li><strong>Slot</strong> — storage slot within the store.</li>
	<li><strong>Currency</strong> — line currency.</li>
	<li><strong>Net Value</strong> — line total price.</li>
</ul>

<p>
	While status is <em>In Preparation</em>, Procurement can edit quantities, stores, and prices.
	After approval, lines are locked except where your process allows amendments.
</p>
<p>
	At the bottom of the Items tab, expand <strong>Additional Charges</strong> to add extra costs (freight, handling) on the PO.
</p>
<p><strong>Role:</strong> Procurement sets up lines; Store Manager reads Pending/Received quantities when receiving goods.</p>

<h2>Approvals tab</h2>
<p>
	Internal sign-off required before the PO can be sent to the supplier.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/po-approvals-awaiting.png',
	'caption' => 'Approvals tab — PO awaiting sign-off from Finance / Lab Manager.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/po-approvals-actions.png',
	'caption' => 'Approver actions — Approve, Reject, or Return.',
])

<h3>Sending for approval</h3>
<p><strong>Who:</strong> Any user who can edit the PO while it is <em>In Preparation</em>.</p>
<ol>
	<li>Confirm General details and Items are correct.</li>
	<li>Click <strong>Get Approval</strong> (top right).</li>
	<li>Status becomes <strong>Awaiting Approval</strong>.</li>
</ol>

<h3>Approver actions</h3>
<p><strong>Who:</strong> User assigned to the configured role (for example Lab Manager under Finance approval).</p>
<ul>
	<li><strong>Approve</strong> — accept the PO. When all steps are done, status becomes <strong>Approval Complete</strong>.</li>
	<li><strong>Reject</strong> — decline the order.</li>
	<li><strong>Return</strong> — send back to Procurement for corrections.</li>
	<li><strong>Change</strong> — reassign the approver (if permitted).</li>
	<li><strong>Send Reminder</strong> — nudge the pending approver.</li>
</ul>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/po-approval-complete.png',
	'caption' => 'Approval Complete — Send Purchase Order and Create Goods Receipt available.',
])

<h2>Notes tab</h2>
<ul>
	<li><strong>+ Note</strong> — add an internal comment.</li>
	<li><strong>Remove</strong> — delete selected notes.</li>
	<li>Columns: <strong>Note Type</strong>, <strong>Created By</strong>, <strong>Created On</strong>.</li>
</ul>

<h2>Attachments tab</h2>
<ul>
	<li><strong>+ Attachment</strong> — upload supporting files.</li>
	<li><strong>Remove</strong> — delete selected files.</li>
	<li>Columns: <strong>File</strong>, <strong>Title</strong>, <strong>Type</strong>, <strong>Created By</strong>, <strong>Created On</strong>.</li>
</ul>

<h2>Requisition Flow tab</h2>
<p>
	Shows the full document chain and is where you open the PO PDF.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/po-requisition-flow.png',
	'caption' => 'Requisition Flow — PR, RFQ, and PO linked; Goods Receipt not yet created.',
])

<ul>
	<li><strong>Purchase Request</strong> — source PR (Completed).</li>
	<li><strong>Request for Quotation</strong> — source RFQ (Completed or Approval Complete).</li>
	<li><strong>Purchase Orders</strong> — this PO (highlighted with blue border when current).</li>
	<li><strong>Goods Receipt</strong> — <em>Not yet at this stage</em> until store receives goods.</li>
</ul>
<p>
	Each card has <strong>Source</strong> (parent document link) and <strong>View</strong> (open that record).
</p>

<h2>How to view the PO PDF</h2>
<p>
	After the PO is approved and sent, you can generate and view the printable purchase order document.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/po-requisition-flow-pdf.png',
	'caption' => 'Generate PDF from Requisition Flow — click the eye icon on the PO card or Generate PDF.',
])

<ol>
	<li>Open the PO and go to the <strong>Requisition Flow</strong> tab.</li>
	<li>Click the <strong>eye icon</strong> on the PO card, or click <strong>Generate PDF</strong> (top right).</li>
	<li>The system shows <strong>Creating PDF…</strong> while the document is built.</li>
	<li>When ready, the PDF opens in a new browser tab.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/po-pdf-document.png',
	'caption' => 'Printed Purchase Order — vendor details, delivery address, line items, and totals.',
])

<p>The PDF includes:</p>
<ul>
	<li><strong>Company header</strong> — your organisation name, address, and logo.</li>
	<li><strong>Vendor details</strong> — supplier name, address, phone, email.</li>
	<li><strong>Delivery instructions</strong> — delivery address, receiver name, department.</li>
	<li><strong>PO number and date</strong> — for example PO20260005, 28 August 2026.</li>
	<li><strong>Payment / INCO terms</strong> — if configured.</li>
	<li><strong>Invoice-to address</strong> — where the supplier should send invoices.</li>
	<li><strong>Line items table</strong> — quantity, item code, description, unit price, and line total.</li>
</ul>
<p>
	Use the <strong>Print</strong> button at the top of the PDF page to print or save as PDF locally.
	If header details look wrong, ask an administrator to update organisation document settings.
</p>
<p><strong>Role:</strong> Procurement typically generates and sends the PDF; approvers may review it before sign-off.</p>

<h2>Send Purchase Order to supplier</h2>
<p>
	Once the PO is <strong>Approval Complete</strong>, Procurement sends it to the supplier.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/po-approval-complete-general.png',
	'caption' => 'Approval Complete — action buttons for sending and receiving.',
])

<ol>
	<li>Click <strong>Send Purchase Order</strong> (top right).</li>
	<li>The PO is emailed to the supplier (with the PDF attached when available).</li>
	<li>Status becomes <strong>Purchase Order Sent</strong>.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/po-sent.png',
	'caption' => 'Purchase Order Sent — ready for goods receipt when delivery arrives.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/po-sent-general.png',
	'caption' => 'PO Sent — Create Goods Receipt and Create Return Note visible for Store Manager.',
])

<p>
	After sending, the supplier uses the PO as authority to deliver.
	<strong>Send Purchase Order</strong> is no longer shown; instead <strong>Create Goods Receipt</strong> appears for store staff.
</p>
<p><strong>Role:</strong> Procurement only.</p>

<h2>Create Goods Receipt — overview</h2>
<p>
	When goods arrive, the <strong>Store Manager</strong> records what was delivered against the PO.
	This creates a <strong>Goods Receipt (GR)</strong> linked to the PO.
	You can receive goods in one go (<strong>full receiving</strong>) or across several deliveries (<strong>partial receiving</strong>).
</p>
<p>
	<strong>Create Goods Receipt</strong> shows when PO status is <em>Approval Complete</em>, <em>Purchase Order Sent</em>, or <em>Goods Accepted</em> — and there is still quantity pending to receive.
</p>

<h2>Create Goods Receipt — step by step</h2>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/po-create-goods-receipt-modal.png',
	'caption' => 'Create Goods Receipt modal — quantities, documents, and verifier.',
])

<ol>
	<li>Open the PO (status <em>Purchase Order Sent</em> or later).</li>
	<li>Click <strong>Create Goods Receipt</strong> (top right).</li>
	<li>The <strong>Create Goods Receipt</strong> modal opens with <strong>Receivable Items</strong>.</li>
</ol>

<h3>Receivable Items table</h3>
<ul>
	<li><strong>Item</strong> — product name.</li>
	<li><strong>Requested Quantity</strong> — total ordered on the PO.</li>
	<li><strong>Pending Quantity</strong> — how much is still not received. This is the maximum you can enter in this GR.</li>
	<li><strong>Receiving Quantity</strong> — how much you are receiving <em>now</em>. Defaults to the full pending amount.</li>
	<li><strong>Expiry</strong> — expiry date (required for items in expiring categories).</li>
	<li><strong>Lot Number</strong> — batch/lot reference (required for expiring items).</li>
	<li><strong>Date of Manufacture</strong> — manufacture date when applicable.</li>
</ul>

<h3>Accompanying Documents</h3>
<ul>
	<li><strong>Invoice</strong> — supplier invoice file.</li>
	<li><strong>Items Checked By</strong> — staff member who physically verified the delivery (required).</li>
	<li><strong>Delivery Note</strong> — delivery note from the carrier or supplier.</li>
	<li><strong>Job Card</strong> — job card if used in your process.</li>
</ul>
<p>
	Your site may require an invoice plus at least one other document (delivery note or job card) before the GR can be created.
</p>

<h3>Message to Requester (optional)</h3>
<p>
	Add a note to the original requester — they receive an email with an OTP to come and verify the goods at the store.
</p>

<ol start="4">
	<li>Fill in receiving quantities and upload documents.</li>
	<li>Click <strong>Create</strong>.</li>
	<li>A new Goods Receipt is created (for example <em>GR20260006</em>) in <strong>Awaiting Approval</strong> status.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/po-goods-receipt-created.png',
	'caption' => 'Goods Receipt created from PO — GR awaits approval; PO Items tab updates received quantities.',
])

<p><strong>Role:</strong> Store Manager (Inventory Store Manager Group) only.</p>

<h2>Full receiving</h2>
<p>
	<strong>Full receiving</strong> means you receive everything still pending on the PO in a single Goods Receipt.
</p>
<ol>
	<li>Open <strong>Create Goods Receipt</strong> on the PO.</li>
	<li>Leave <strong>Receiving Quantity</strong> equal to <strong>Pending Quantity</strong> for each line (this is the default).</li>
	<li>Complete expiry, lot number, documents, and <strong>Items Checked By</strong>.</li>
	<li>Click <strong>Create</strong>.</li>
</ol>
<p>
	After the GR is approved and goods are accepted into store:
</p>
<ul>
	<li><strong>Pending Quantity</strong> on the PO Items tab becomes <strong>0</strong>.</li>
	<li><strong>Received Quantity</strong> matches the <strong>Requested Quantity</strong>.</li>
	<li><strong>Create Goods Receipt</strong> disappears from the PO (nothing left to receive).</li>
	<li>PO status may move to <strong>Goods Accepted</strong>.</li>
</ul>

<h2>Partial receiving</h2>
<p>
	<strong>Partial receiving</strong> means the supplier delivers less than the full order, or you receive in batches over time.
</p>
<ol>
	<li>Open <strong>Create Goods Receipt</strong> on the PO.</li>
	<li>For each line, set <strong>Receiving Quantity</strong> to the amount delivered <em>today</em> — less than the <strong>Pending Quantity</strong>.
		<ul>
			<li>Example: PO ordered 7 units; 3 arrive today → enter <strong>3</strong> in Receiving Quantity.</li>
		</ul>
	</li>
	<li>Complete documents and click <strong>Create</strong>.</li>
	<li>GR is created for the partial amount.</li>
</ol>
<p>On the PO Items tab after partial receipt:</p>
<ul>
	<li><strong>Received Quantity</strong> increases by the amount just received.</li>
	<li><strong>Pending Quantity</strong> shows what is still outstanding (for example 4 remaining of 7).</li>
	<li><strong>Create Goods Receipt</strong> stays available so you can receive the rest later.</li>
</ul>
<p>
	When the next delivery arrives, repeat <strong>Create Goods Receipt</strong> and enter the new quantities.
	Each delivery creates a separate GR linked to the same PO.
	Continue until <strong>Pending Quantity</strong> is zero on all lines.
</p>
<p>
	You cannot enter a receiving quantity greater than the pending quantity — the system blocks over-receiving.
</p>

<h2>Multiple Goods Receipts on Requisition Flow</h2>
<p>
	After one or more GRs are created, open the PO <strong>Requisition Flow</strong> tab.
	The <strong>Goods Receipt</strong> column lists every GR raised against this PO, each with its own status.
	Click <strong>View</strong> on any GR to open it.
</p>
<p>
	Continue the receiving process in the <strong>Goods Receipt</strong> chapter — approvals, accepting goods into store, and finance steps.
</p>

<h2>Other PO actions</h2>
<ul>
	<li><strong>Create Return Note</strong> — Store Manager can start a goods return to the supplier (shown alongside Create Goods Receipt).</li>
	<li><strong>Mark as Complete</strong> — Procurement closes the PO when the buying process is finished.</li>
	<li><strong>Make Amendment</strong> — available on sent POs when your process allows order changes (Procurement).</li>
</ul>

<h2>PO status summary</h2>
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
			<td>PO created; being reviewed</td>
			<td>Procurement — check details, click Get Approval</td>
		</tr>
		<tr>
			<td><em>Awaiting Approval</em></td>
			<td>With approver(s)</td>
			<td>Approver — Approve, Reject, or Return</td>
		</tr>
		<tr>
			<td><em>Partially Approved</em></td>
			<td>Some approval steps done</td>
			<td>Next approver in sequence</td>
		</tr>
		<tr>
			<td><em>Approval Complete</em></td>
			<td>Approved; not yet sent</td>
			<td>Procurement — Send Purchase Order</td>
		</tr>
		<tr>
			<td><em>Purchase Order Sent</em></td>
			<td>Emailed to supplier</td>
			<td>Store Manager — Create Goods Receipt when goods arrive</td>
		</tr>
		<tr>
			<td><em>Goods Accepted</em></td>
			<td>Goods received into store (may be partial or full)</td>
			<td>Store Manager — further GR if pending qty remains; or Procurement — Mark as Complete</td>
		</tr>
		<tr>
			<td><em>Completed</em></td>
			<td>PO closed</td>
			<td>None — archived on Completed tab</td>
		</tr>
	</tbody>
</table>

<h2>End-to-end PO process (quick reference)</h2>
<ol>
	<li><strong>RFQ approved</strong> → Procurement clicks Create Purchase Order.</li>
	<li><strong>General + Items</strong> → review supplier, lines, prices, delivery date.</li>
	<li><strong>Get Approval</strong> → approver signs off.</li>
	<li><strong>Send Purchase Order</strong> → supplier receives the order.</li>
	<li><strong>View PDF</strong> → Requisition Flow → eye icon or Generate PDF.</li>
	<li><strong>Goods arrive</strong> → Store Manager → Create Goods Receipt (full or partial).</li>
	<li><strong>GR approved</strong> → goods accepted into store; repeat GR if more deliveries expected.</li>
	<li><strong>All received</strong> → Procurement marks PO complete.</li>
</ol>

<h2>Tips</h2>
<ul>
	<li>Check <strong>Pending Quantity</strong> on the Items tab before each receipt — it tells you exactly how much is left.</li>
	<li>For partial deliveries, create a GR for each shipment; do not wait to receive everything at once.</li>
	<li>Always fill <strong>Items Checked By</strong> and attach invoice plus delivery note where required.</li>
	<li>Use <strong>Requisition Flow</strong> to trace any PO back to its PR and RFQ.</li>
	<li>Generate the PO PDF from Requisition Flow after sending — useful for records and supplier follow-up.</li>
</ul>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-package-variant-closed"></i></span>
	<p>
		After creating a Goods Receipt, continue in the <strong>Goods Receipt</strong> chapter for approvals, store/slot assignment, and accepting goods into stock.
	</p>
</div>
