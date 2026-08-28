<h2>What is a Goods Receipt?</h2>
<p>
	A <strong>Goods Receipt (GR)</strong> records that ordered items have physically arrived at your site.
	It is created from an approved <strong>Purchase Order</strong> by store staff.
	The GR goes through internal approval, physical verification, and finally <strong>Accept Goods</strong>
	before stock appears in inventory.
</p>
<p>
	Open Goods Receipts under <strong>Inventory Management → Request to Order → Goods Receipt</strong>.
	Most GRs are created from a PO — see the <a href="{{ route('usermanual.show', ['manual' => 'inventory', 'chapter' => 'purchase-order']) }}">Purchase Order</a> chapter for creation and partial receiving.
</p>

<h2>Who does what</h2>
<table class="table table-bordered table-sm">
	<thead>
		<tr>
			<th>Role</th>
			<th>Typical GR tasks</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><strong>Store Manager</strong> (Inventory Store Manager Group)</td>
			<td>Create GR from PO, upload documents, choose <em>Items Checked By</em>, assign store/slot, click <strong>Accept Goods</strong>, click <strong>Send to Finance</strong></td>
		</tr>
		<tr>
			<td><strong>Items verifier</strong> (<em>Items Checked By</em> on the GR)</td>
			<td>Physically inspect the delivery, then click <strong>Confirm Verified Items</strong></td>
		</tr>
		<tr>
			<td><strong>Original requester</strong> (person who raised the Purchase Request)</td>
			<td>Receives the OTP when the GR is created; shares the code at the store so goods can be accepted into inventory</td>
		</tr>
		<tr>
			<td><strong>Approver</strong> (role under GR Approval Configuration)</td>
			<td>Approve, Reject, or Return the GR while it is <em>Awaiting Approval</em></td>
		</tr>
		<tr>
			<td><strong>Finance</strong> (Inventory Finance Group)</td>
			<td>Review GR sent for payment, click <strong>Mark as Complete</strong> when the invoice is processed</td>
		</tr>
		<tr>
			<td><strong>Procurement</strong></td>
			<td>May monitor progress on Requisition Flow; not usually the person who accepts goods</td>
		</tr>
	</tbody>
</table>

<h2>End-to-end process (correct order)</h2>
<p>Follow these steps in order. Stock is <strong>not</strong> in inventory until step 5 is complete.</p>
<ol>
	<li><strong>Store Manager</strong> creates the GR from the PO (quantities, documents, Items Checked By).</li>
	<li>System sends an <strong>OTP to the original requester</strong> and emails the <strong>items verifier</strong>.</li>
	<li><strong>Approver</strong> approves the GR → status becomes <em>Approval Complete</em>.</li>
	<li><strong>Items verifier</strong> clicks <strong>Confirm Verified Items</strong>.</li>
	<li><strong>Store Manager</strong> opens the <strong>Items</strong> tab and selects <strong>Store</strong> and <strong>Slot</strong> for each line.</li>
	<li><strong>Store Manager</strong> clicks <strong>Accept Goods</strong> → enters the requester's OTP → rates the supplier → clicks <strong>Confirm</strong>. Stock is now in inventory.</li>
	<li><strong>Store Manager</strong> clicks <strong>Send to Finance</strong> → <strong>Confirm</strong>.</li>
	<li><strong>Finance</strong> opens the GR, reviews attachments, clicks <strong>Mark as Complete</strong>.</li>
</ol>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-information"></i></span>
	<p>
		<strong>Important:</strong> Approval alone does <em>not</em> put stock into the store.
		You must complete verification, store/slot assignment, and <strong>Accept Goods</strong> with the OTP.
	</p>
</div>

<h2>Stage 1 — GR approval</h2>
<p>
	When a Store Manager creates a GR from a PO, the new record starts in <strong>Awaiting Approval</strong>.
	The linked PO and Requisition Flow update to show the new GR with its attached documents (invoice, delivery note, job card).
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/gr-approvals-awaiting.png',
	'caption' => 'Approvals tab — GR awaits sign-off from the configured approver (for example Lab Manager).',
])

<p>On the <strong>Approvals</strong> tab:</p>
<ul>
	<li><strong>Required Approvals</strong> shows how many steps must be completed.</li>
	<li>Each row lists the <strong>Approval/Role</strong>, who it was <strong>Approved By</strong> (assigned person), and action buttons.</li>
	<li>The assigned approver clicks <strong>Approve</strong> (green), <strong>Reject</strong> (red), or <strong>Return</strong> (blue) to send the record back for corrections.</li>
	<li><strong>Send Reminder</strong> nudges the pending approver if the GR is waiting too long.</li>
</ul>
<p><strong>Role:</strong> Approver (the person assigned in the Approvals table — for example a Lab Manager in the procurement approval step).</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/gr-requisition-flow-awaiting.png',
	'caption' => 'Requisition Flow — GR linked to PR → RFQ → PO with document checklist while awaiting approval.',
])

<p>
	After all approval steps are done, the GR status changes to <strong>Approval Complete</strong>.
	At this point stock is still <strong>not</strong> in inventory.
</p>

<h2>Stage 2 — Items verifier confirms the delivery</h2>
<p>
	When the GR was created, the Store Manager selected <strong>Items Checked By</strong> — the staff member who physically inspected the delivery.
	That person must now confirm in the system that the goods match what was ordered.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/gr-approval-complete-general.png',
	'caption' => 'Approval Complete — Accept Goods is available; verifier must confirm items first.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/gr-confirm-verified-items.png',
	'caption' => 'Confirm Verified Items button — visible only to the Items Checked By person.',
])

<h3>Step-by-step: Confirm Verified Items</h3>
<ol>
	<li>The <strong>Items Checked By</strong> person (items verifier) opens the GR.</li>
	<li>Confirm the status badge reads <strong>Approval Complete</strong>.</li>
	<li>Click <strong>Confirm Verified Items</strong> (green checkmark button, top of the page).</li>
	<li>Confirm the prompt: <em>Are you sure that you confirm to verifying these items?</em></li>
	<li>A green message appears: <strong>Requester Confirmation has been completed.</strong></li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/gr-requester-confirmed.png',
	'caption' => 'After verification — success toast and Accept Goods ready for the Store Manager.',
])

<p><strong>Role:</strong> Items verifier only (the user set in <em>Items Checked By</em> when the GR was created).</p>
<p>
	If you are not the items verifier, you see <strong>Awaiting Requester Confirmation</strong> instead of the button.
	Wait for the verifier to complete this step before accepting goods.
</p>

<h2>Stage 3 — Assign store and slot (Items tab)</h2>
<p>
	Before accepting goods, every line on the GR must have a physical location in the store.
</p>
<ol>
	<li><strong>Store Manager</strong> opens the <strong>Items</strong> tab.</li>
	<li>For each line, select a <strong>Store</strong> (which store room or cupboard).</li>
	<li>Select a <strong>Slot</strong> (the specific shelf or bin within that store).</li>
	<li>Confirm <strong>Received Quantity</strong>, <strong>Expiry</strong>, and <strong>Lot Number</strong> are correct (set when the GR was created; edit if needed).</li>
	<li>Click <strong>Save</strong> (top right) if you changed any values.</li>
</ol>
<p>
	Store and Slot become <strong>required</strong> once the GR is past <em>Awaiting Approval</em>.
	If either is missing, <strong>Accept Goods</strong> will not complete successfully.
</p>
<p><strong>Role:</strong> Store Manager.</p>

<h2>The OTP — when it is sent and who receives it</h2>
<p>
	The OTP (One-Time Password) is a <strong>6-digit code</strong> generated automatically when the Store Manager
	<strong>creates</strong> the Goods Receipt from the Purchase Order.
	It is <strong>not</strong> sent when the GR is approved — it is sent at creation time.
</p>

<h3>Who receives the OTP?</h3>
<table class="table table-bordered table-sm">
	<thead>
		<tr>
			<th>Person</th>
			<th>What they receive</th>
			<th>When</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><strong>Original requester</strong><br><small>(person who raised the Purchase Request)</small></td>
			<td>
				Email and SMS with the <strong>6-digit OTP code</strong>.<br>
				Message explains that goods from their requisition have arrived at the store and they should use the code when verifying the delivery.
			</td>
			<td>Immediately when the GR is created</td>
		</tr>
		<tr>
			<td><strong>Items Checked By</strong><br><small>(items verifier)</small></td>
			<td>
				Email asking them to open the GR and <strong>confirm their verification</strong> of the items.<br>
				<em>They do not receive the OTP code.</em>
			</td>
			<td>Immediately when the GR is created</td>
		</tr>
	</tbody>
</table>

<h3>Why is there an OTP?</h3>
<p>
	The OTP proves that the <strong>original requester</strong> (the person who asked for the items) agrees the correct goods were delivered.
	The Store Manager collects this code from the requester at the store (or by phone) and enters it when clicking <strong>Accept Goods</strong>.
	Without a valid OTP, stock cannot be added to inventory.
</p>

<h3>Practical tips for the OTP</h3>
<ul>
	<li>Ask the original requester to check their <strong>email</strong> and <strong>SMS</strong> when goods arrive.</li>
	<li>The code is tied to this specific GR — you cannot reuse an OTP from a different receipt.</li>
	<li>If the requester did not receive the code, check their contact details in the system or ask an administrator to resend.</li>
	<li>The Store Manager enters the code — the requester does not log in to enter it themselves (unless they also happen to be the Store Manager).</li>
</ul>

<h2>Stage 4 — Accept Goods (stock into inventory)</h2>
<p>
	Once the items verifier has confirmed and store/slot are set, the Store Manager accepts the goods into inventory.
</p>

<ol>
	<li>Open the GR (status <strong>Approval Complete</strong>).</li>
	<li>Click <strong>Accept Goods</strong> (top right).</li>
	<li>The <strong>Rate Supplier and Confirm Receipt</strong> modal opens.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/gr-accept-goods-modal.png',
	'caption' => 'Accept Goods modal — rate supplier, enter Requester OTP, click Confirm.',
])

<h3>Inside the modal</h3>
<ol>
	<li><strong>Rate the supplier</strong> — move the sliders for each criterion (for example <em>Communication</em>, <em>Quality</em>). Each criterion has a score out of 10 and a <strong>Reason for your rating</strong> text box. Fill in honest feedback — this feeds supplier performance records.</li>
	<li><strong>Enter the Requester OTP</strong> — type the 6-digit code the original requester received when the GR was created.</li>
	<li>Click <strong>Confirm</strong> (green).</li>
</ol>

<p>On success:</p>
<ul>
	<li>Status changes to <strong>Goods Accepted</strong>.</li>
	<li>A message appears: <em>Items issued out and supplier criteria score updated.</em></li>
	<li>Stock is now visible in the assigned store and slot.</li>
	<li>The original requester receives a confirmation email that items have been added to inventory.</li>
</ul>

<p><strong>Role:</strong> Store Manager (or any user with permission to edit Goods Receipts — typically store staff).</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/gr-goods-accepted.png',
	'caption' => 'Goods Accepted — stock is in inventory; Send to Finance is now available.',
])

<h2>Stage 5 — Send to Finance</h2>
<p>
	After goods are accepted, the Store Manager sends the Goods Receipt Note (GRN) to Finance so they can process the supplier invoice for payment.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/gr-send-to-finance-button.png',
	'caption' => 'Goods Accepted — Reverse Goods Receipt and Send to Finance buttons.',
])

<ol>
	<li>Confirm the GR status is <strong>Goods Accepted</strong>.</li>
	<li>Check the <strong>Attachments</strong> tab has the invoice, delivery note, and any other required documents.</li>
	<li>On the <strong>General</strong> tab, fill in <strong>Delivery Note No</strong> and <strong>Supplier Invoice No</strong> if not already entered.</li>
	<li>Confirm the <strong>Cost Center</strong> is correct (for example <em>Finance</em>).</li>
	<li>Click <strong>Send to Finance</strong> (top right).</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/gr-send-to-finance-modal.png',
	'caption' => 'Send to Finance confirmation — sends the GRN to finance to process the invoice.',
])

<ol start="6">
	<li>Read the message: <em>Send GRN to finance to process the invoice.</em></li>
	<li>Click <strong>Confirm</strong>.</li>
</ol>

<p>What happens next:</p>
<ul>
	<li>GR status changes to <strong>Awaiting Finance Approval</strong>.</li>
	<li>All users in the <strong>Finance</strong> workflow group receive an email with subject <em>[GR code] Invoice Awaiting Processing</em> and a link to open the GR.</li>
</ul>

<p><strong>Role:</strong> Store Manager.</p>

<h2>Stage 6 — Finance marks the GR complete</h2>
<p>
	Finance staff review the GR, match it to the supplier invoice, and close the record when payment processing is done.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/gr-awaiting-finance.png',
	'caption' => 'Awaiting Finance Approval — Finance user sees Mark as Complete.',
])

<ol>
	<li><strong>Finance</strong> user opens the GR from the email link or <strong>Goods Receipt</strong> list.</li>
	<li>Confirm status is <strong>Awaiting Finance Approval</strong>.</li>
	<li>Review <strong>General</strong> details, <strong>Items</strong>, and <strong>Attachments</strong> (invoice, delivery note).</li>
	<li>Click <strong>Mark as Complete</strong> (top right).</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/gr-mark-complete-modal.png',
	'caption' => 'Mark GR as Complete — final confirmation by Finance.',
])

<ol start="5">
	<li>Confirm: <em>Are you sure that you want to continue with this action?</em></li>
	<li>Click <strong>Yes, Continue</strong>.</li>
</ol>

<p>On success:</p>
<ul>
	<li>GR status changes to <strong>Completed</strong>.</li>
	<li>The linked Purchase Order may also be marked complete if all items are fully received.</li>
	<li>The procurement cycle for this delivery is finished.</li>
</ul>

<p><strong>Role:</strong> Finance (Inventory Finance Group) only.</p>

<h2>GR record — tabs overview</h2>
<p>Each Goods Receipt has seven tabs:</p>
<ol>
	<li><strong>General</strong> — description, company unit, department, cost center, carrier, gate pass, delivery note number, supplier invoice number.</li>
	<li><strong>Items</strong> — received lines with quantities, store, slot, expiry, and lot number.</li>
	<li><strong>Approvals</strong> — approval workflow while <em>Awaiting Approval</em>.</li>
	<li><strong>Notes</strong> — internal comments.</li>
	<li><strong>Attachments</strong> — invoice, delivery note, job card uploaded at creation.</li>
	<li><strong>Requisition Flow</strong> — visual chain from PR → RFQ → PO → GR with document checklist.</li>
	<li><strong>Rate Supplier</strong> — view or update supplier ratings after acceptance.</li>
</ol>

<h2>Status summary</h2>
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
			<td><strong>Awaiting Approval</strong></td>
			<td>GR created; internal sign-off needed</td>
			<td>Approver</td>
		</tr>
		<tr>
			<td><strong>Approval Complete</strong></td>
			<td>Approved; stock <em>not yet</em> in inventory</td>
			<td>Items verifier → Store Manager</td>
		</tr>
		<tr>
			<td><strong>Goods Accepted</strong></td>
			<td>Stock is in the store; ready for finance handoff</td>
			<td>Store Manager (Send to Finance)</td>
		</tr>
		<tr>
			<td><strong>Awaiting Finance Approval</strong></td>
			<td>Sent to Finance for invoice processing</td>
			<td>Finance (Mark as Complete)</td>
		</tr>
		<tr>
			<td><strong>Completed</strong></td>
			<td>Fully closed</td>
			<td>—</td>
		</tr>
	</tbody>
</table>

<h2>Reverse Goods Receipt</h2>
<p>
	If goods were accepted in error, a Store Manager can click <strong>Reverse Goods Receipt</strong>
	when the status is <em>Goods Accepted</em>, <em>Awaiting Finance Approval</em>, or <em>Completed</em>.
	This rolls back the receipt — use only when something was wrong with quantities, items, or locations.
</p>

<h2>Partial deliveries</h2>
<p>
	If only part of a PO arrives, the Store Manager creates a GR for the quantity received now.
	After that GR is accepted, the PO still shows a <strong>Pending Quantity</strong> for the balance.
	Create another GR when the rest of the delivery arrives.
	See the <a href="{{ route('usermanual.show', ['manual' => 'inventory', 'chapter' => 'purchase-order']) }}">Purchase Order</a> chapter for full and partial receiving examples.
</p>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-package-variant"></i></span>
	<p>
		<strong>Remember the order:</strong> Approve → Verifier confirms → Store/Slot → Accept Goods with OTP → Send to Finance → Finance completes.
		Skipping a step leaves stock invisible or blocks finance from processing the invoice.
	</p>
</div>
