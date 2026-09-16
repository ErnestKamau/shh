<h2>What is Request to Store?</h2>
<p>
	<strong>Request to Store (RS)</strong> is how a department asks for items that are
	<strong>already held in the store</strong>. You are not buying from a supplier;
	you are asking store staff to issue stock that is on hand.
</p>
<p>
	Open <strong>Inventory Management → Request to Store</strong> in the sidebar.
	After approval, store staff create a <strong>Material Issuance (MI)</strong> from the request,
	then confirm pickup with a one-time code (OTP) before stock is deducted.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/request-to-store.png',
	'caption' => 'Create Request to Store — General tab while the request is still In Preparation.',
])

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-cart-outline"></i></span>
	<p>
		If the store has nothing left (or never stocked the item), raise a
		<a href="{{ route('usermanual.show', ['manual' => 'inventory', 'chapter' => 'purchase-request']) }}">Purchase Request</a>
		under Request to Order instead.
	</p>
</div>

<h2>When to use it</h2>
<ul>
	<li>The item exists in a store (check <strong>Stores → Contents</strong> or <strong>Open Quantity</strong> on the Items tab).</li>
	<li>You need it for day-to-day work and do not need a new purchase.</li>
</ul>

<h2>Who does what</h2>
<table class="table table-bordered table-sm">
	<thead>
		<tr>
			<th>Role</th>
			<th>Typical tasks</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><strong>Requester</strong> (department staff)</td>
			<td>Create the Request to Store, add items and quantities, click <strong>Get Approval</strong>, collect the OTP email when items are ready</td>
		</tr>
		<tr>
			<td><strong>Approver</strong> (role under Request to Store Approval Configuration — for example Issuance Approver)</td>
			<td>Approve, Reject, or Return the request while it is <em>Awaiting Approval</em></td>
		</tr>
		<tr>
			<td><strong>Store staff</strong> (Inventory Store Manager or equivalent)</td>
			<td>After approval: <strong>Create Material Issuance</strong>, set issuing quantities, click <strong>Issue Items</strong>, enter the requester OTP to complete pickup</td>
		</tr>
	</tbody>
</table>

<h2>End-to-end process (correct order)</h2>
<ol>
	<li><strong>Requester</strong> creates a Request to Store, fills General and Items, and clicks <strong>Save</strong>.</li>
	<li><strong>Requester</strong> clicks <strong>Get Approval</strong> → status becomes <em>Awaiting Approval</em>.</li>
	<li><strong>Approver</strong> opens the Approvals tab and clicks <strong>Approve</strong> → status becomes <em>Approval Complete</em>.</li>
	<li><strong>Store staff</strong> clicks <strong>Create Material Issuance</strong>, enters issuing quantities, and confirms.</li>
	<li>System creates a Material Issuance in <em>Awaiting User Reception</em> and emails the requester a <strong>6-digit OTP</strong>.</li>
	<li><strong>Store staff</strong> clicks <strong>Issue Items</strong>, enters the OTP from the requester, and confirms → MI status becomes <em>Completed</em> and stock is reduced.</li>
	<li>Check <strong>Inventory Movement</strong> for the stock-out audit trail.</li>
</ol>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-information"></i></span>
	<p>
		<strong>Important:</strong> Approval alone does <em>not</em> remove stock from the store.
		Stock is only deducted after Material Issuance is completed with a valid OTP.
	</p>
</div>

<h2>Sidebar stages</h2>
<p>
	Expand <strong>Request to Store</strong> in the Inventory sidebar. You will see two stages:
</p>
<div class="um-compare">
	<div class="um-compare__card">
		<h4>1. Request to Store</h4>
		<p>Department asks for stock on hand. Create, approve, then hand over to store for issuance.</p>
	</div>
	<div class="um-compare__card">
		<h4>2. Material Issuance</h4>
		<p>Store prepares items for pickup, sends the OTP, and confirms collection so stock is reduced.</p>
	</div>
</div>
<p>
	Red badge numbers beside each stage show how many records need attention.
	You can also open a Material Issuance from the parent Request to Store via
	<strong>Create Material Issuance</strong> or <strong>Requisition Flow</strong>.
</p>

<h2>The Request to Store list</h2>
<p>
	Open <strong>Inventory Management → Request to Store → Request to Store</strong>.
	The main screen has four tabs: <strong>Requests</strong>, <strong>Completed</strong>,
	<strong>Frequent Requests</strong>, and <strong>Approval Configuration</strong>.
	Use <strong>+ Add</strong> (top right) to create a new request.
</p>

<h3>Requests tab — column by column</h3>
<ul>
	<li><strong>#</strong> — row number.</li>
	<li><strong>Checkbox / indicator</strong> — select rows; coloured bar shows status at a glance.</li>
	<li><strong>Code</strong> — the RS reference (for example <em>RS20260002</em>). Click to open the record.</li>
	<li><strong>Items</strong> — summary of item names on the request.</li>
	<li><strong>Priority</strong> — urgency (for example Normal).</li>
	<li><strong>Status</strong> — lifecycle stage (for example <em>In Preparation</em>, <em>Awaiting Approval</em>, <em>Approval Complete</em>).</li>
	<li><strong>Description</strong> — purpose text from the General tab.</li>
	<li><strong>Due Date</strong> — where set on the request.</li>
	<li><strong>Created By</strong> — who raised it.</li>
	<li><strong>Department</strong> — requester’s department.</li>
	<li><strong>Created On</strong> — first save time.</li>
	<li><strong>Approvals</strong> — approval progress indicator.</li>
	<li><strong>Total Value</strong> — value summary where calculated.</li>
</ul>
<p>
	Use <strong>Search</strong> and <strong>Show entries</strong> to filter a long list.
	Export buttons (Copy, CSV, Excel, PDF, Print) download the table for reporting.
</p>

<h3>Completed tab</h3>
<p>
	Shows Request to Store records that have finished their life on this stage
	(for example after all pending quantities were issued).
	Use it to look up historical requests without scrolling through active ones.
</p>

<h3>Frequent Requests tab</h3>
<p>
	Stores reusable / frequently raised store requests so you can clone common item sets
	instead of building the same lines every time.
	Open a frequent request and use it as a starting point when your organisation has enabled this.
</p>

<h2>Approval Configuration</h2>
<p>
	Before anyone can approve Request to Store records, an administrator sets up
	<strong>who is allowed to approve</strong>.
	Open the <strong>Approval Configuration</strong> tab on the Request to Store list
	(same pattern as Purchase Request — not under Configurations in the sidebar).
</p>
<ul>
	<li><strong>Title</strong> — label for this step (for example <em>Issuance Approver</em>). Shown on the Approvals tab of each request.</li>
	<li><strong>Users</strong> — everyone assigned to the linked role (name and email). Any of these users can act when the request reaches their step.</li>
	<li><strong>Edit (pencil)</strong> — change the title, role, or approval level.</li>
	<li><strong>+ (top right)</strong> — add a new approval step.</li>
</ul>
<p>
	When adding a step: enter an <strong>Approval Title</strong>, select the <strong>Role</strong> whose members may approve,
	and set the <strong>Level</strong> if you have more than one step in sequence.
	The next level only becomes active after the previous step is approved.
</p>
<p>
	<strong>Who sets this up:</strong> typically an Inventory administrator or manager.
	Department staff who only create requests do not need to change these settings.
</p>

<h2>Creating a Request to Store</h2>
<p>
	On the Request to Store list, click <strong>+ Add</strong>.
	You land on <strong>Create Request to Store</strong> with status <strong>In Preparation</strong>.
	The record has six tabs: <strong>General</strong>, <strong>Items</strong>, <strong>Approvals</strong>, <strong>Notes</strong>, <strong>Attachments</strong>, and <strong>Requisition Flow</strong>.
</p>
<p>
	Click <strong>Save</strong> (top right) whenever you change something.
	After the first save, the system assigns a code (for example <em>RS20260002</em>).
</p>

<h2>General tab</h2>
<p>
	The General tab holds the header information for the whole request.
	Required fields are marked with a red asterisk (*).
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rts-create-general.png',
	'caption' => 'General tab — description, company unit, department, cost centre, and priority.',
])

<h3>Left column</h3>
<ul>
	<li><strong>Description/Purpose</strong> — explain why the items are needed. Approvers and store staff read this first.</li>
	<li><strong>Company Unit</strong> — your current location (for example <em>AmSpec Dubai HQ</em> or <em>Amspec Store</em>). Filled from your login.</li>
	<li><strong>Department</strong> — your department. Filled from your user profile.</li>
	<li><strong>Cost Center*</strong> — which budget or cost centre will be charged. Select one or more tags. Required before approval.</li>
	<li><strong>Nature of Purchase*</strong> — shown where your organisation requires it for store requests (same idea as on Purchase Request).</li>
</ul>

<h3>Right column</h3>
<ul>
	<li><strong>Requested By</strong> — the person asking for the stock (usually you).</li>
	<li><strong>Created On</strong> — date and time of the first save. Empty until you save.</li>
	<li><strong>Created By</strong> — who created the record.</li>
	<li><strong>Updated On</strong> — last change time.</li>
	<li><strong>Priority*</strong> — urgency (for example <em>Normal</em>). Use a higher priority only when timing is critical.</li>
</ul>
<p><strong>Role:</strong> the <strong>requester</strong> fills General and Items, then saves.</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rts-saved-in-preparation.png',
	'caption' => 'After Save — request has a code (RS20260002), status still In Preparation, and Get Approval becomes available.',
])

<h2>Items tab</h2>
<p>
	Every product you need from the store is a line on the Items tab.
	You must have at least one item line before you can send the request for approval.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rts-create-items.png',
	'caption' => 'Items tab — select catalogue item, issuing UoM, and requested quantity. Open Quantity shows available stock.',
])

<h3>Adding and removing lines</h3>
<ul>
	<li><strong>+ Add</strong> — inserts a new blank row.</li>
	<li><strong>Remove</strong> — deletes the rows you have ticked.</li>
</ul>

<h3>Columns on each line</h3>
<ul>
	<li><strong>#</strong> — line number.</li>
	<li><strong>Item</strong> — search and select from the inventory catalogue (for example <em>IM0006-Agar Media</em>).</li>
	<li><strong>Brand</strong> — optional preferred brand.</li>
	<li><strong>Description/Location</strong> — extra detail or where the items will be used. The map-pin icon may open a location picker where configured.</li>
	<li><strong>Issuing UoM</strong> — unit of measure for issuance (for example <em>L</em>). Options come from the item’s configured units.</li>
	<li><strong>Open Quantity</strong> — current available stock (read-only, often highlighted). Use this to confirm the store can fulfil your request.</li>
	<li><strong>Requested Quantity</strong> — how much you need. Must not exceed what the store can issue.</li>
	<li><strong>Pending Quantity</strong> — still to be issued after Material Issuance (filled by the system as issuance progresses).</li>
	<li><strong>Issued Quantity</strong> — already issued against this request (system-filled).</li>
</ul>
<p>
	While status is <em>In Preparation</em>, you can edit item fields.
	After you send for approval, lines become read-only unless the request is returned to you.
</p>

<h2>Notes and Attachments</h2>
<p>
	Use <strong>Notes</strong> for internal comments for approvers or store staff.
	Use <strong>Attachments</strong> for supporting files (photos, job cards, specifications) if needed.
	Both tabs are optional.
</p>

<h2>Requisition Flow tab</h2>
<p>
	Shows where this request sits in the store workflow: <strong>Request to Store → Material Issuance</strong>
	(and related documents such as Gate Pass when created).
	As store staff create issuances, new cards appear in the later columns with links to open them.
</p>

<h2>Send for approval</h2>
<p><strong>Who:</strong> Requester.</p>
<ol>
	<li>Confirm required General fields and at least one item line are complete.</li>
	<li>Click <strong>Get Approval</strong> (top right).</li>
	<li>Status changes to <em>Awaiting Approval</em>. Approvers are notified.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rts-awaiting-approval.png',
	'caption' => 'Awaiting Approval — Get Approval has been sent; Approvals tab shows a notification badge.',
])

<h2>Approvals tab</h2>
<p>
	After you click <strong>Get Approval</strong>, each configured approval step appears with its status.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rts-approvals-tab.png',
	'caption' => 'Approvals tab — Issuance Approver can Approve, Reject, or Return. Send Reminder nudges pending approvers.',
])

<ul>
	<li><strong>Required Approvals</strong> — how many steps must be completed.</li>
	<li><strong>Approval</strong> — step title and role (for example <em>Issuance Approver</em> / admin).</li>
	<li><strong>Created At</strong> — when this step was created for the request.</li>
	<li><strong>Approved By</strong> — assigned user (with <strong>Change</strong> where your role allows reassignment).</li>
	<li><strong>Approved At</strong> — blank while pending.</li>
	<li><strong>Actions</strong> — <strong>Approve</strong> (green), <strong>Reject</strong> (red), or <strong>Return</strong> (blue) for the assigned approver.</li>
</ul>
<p>
	<strong>Send Reminder</strong> emails approvers who have not yet acted.
</p>
<p>
	<strong>Approve</strong> — accept; if this was the last required step, status becomes <em>Approval Complete</em>.<br>
	<strong>Reject</strong> — decline; the request does not proceed to issuance.<br>
	<strong>Return</strong> — send back to the requester to fix details; they can edit and resubmit.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rts-approval-complete.png',
	'caption' => 'Approval Complete — Create Material Issuance is available for store staff.',
])

<h2>Create Material Issuance</h2>
<p><strong>Who:</strong> Store staff.</p>
<p>
	When the Request to Store status is <strong>Approval Complete</strong>, open the record and click
	<strong>Create Material Issuance</strong>.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rts-create-mi-modal.png',
	'caption' => 'Create Material Issuance modal — enter Issuing Quantity per line (can be full or partial). Optional message to the requester.',
])

<ol>
	<li>Review each line: <strong>Requested Quantity</strong>, <strong>Pending Quantity</strong>.</li>
	<li>Enter <strong>Issuing Quantity</strong> for this issuance (you may issue less than pending for a partial fulfilment).</li>
	<li>Optionally type a <strong>Message to Requester</strong>.</li>
	<li>Confirm to create the Material Issuance.</li>
</ol>
<p>
	The system opens a new Material Issuance (for example <em>MI20260002</em>) with status
	<strong>Awaiting User Reception</strong>.
	A gate pass may be generated automatically (for example <em>GP20260015</em>).
	You can also find open issuances under
	<strong>Inventory Management → Request to Store → Material Issuance</strong>
	(Requests and Completed tabs, same list layout as other inventory stages).
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/mi-awaiting-reception.png',
	'caption' => 'Material Issuance — Awaiting User Reception. Issue Items is ready once the requester OTP is available.',
])

<h3>Material Issuance General fields</h3>
<ul>
	<li><strong>Description/Purpose</strong> — often summarised from issued items (for example <em>Agar Media(10)</em>).</li>
	<li><strong>Company Unit / Department / Cost Center</strong> — carried from the Request to Store.</li>
	<li><strong>Note Bearer Name</strong> — person collecting the items (optional).</li>
	<li><strong>Gate Pass</strong> — linked gate pass code (system-filled).</li>
	<li><strong>Time Out / Vehicle Number / Destination</strong> — optional logistics details for collection.</li>
</ul>

<h2>The OTP — pickup confirmation</h2>
<p>
	When Material Issuance is created, the system emails the <strong>requester</strong> a
	<strong>6-digit confirmation OTP</strong>.
	The subject looks like: <em>[MI20260002] Items from your Request to Store are available for pick-up.</em>
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/mi-otp-email.png',
	'caption' => 'OTP email — links to the Request to Store and shows the 6-digit code to use at pickup.',
])

<ul>
	<li>The email names the Request to Store code (for example <em>RS20260002</em>) and the Material Issuance code.</li>
	<li>The requester shares the code with store staff at pickup (or by phone).</li>
	<li>The OTP is tied to this Material Issuance — you cannot reuse a code from another MI.</li>
</ul>

<h3>Issue Items with OTP</h3>
<p><strong>Who:</strong> Store staff (with the code from the requester).</p>
<ol>
	<li>Open the Material Issuance.</li>
	<li>Click <strong>Issue Items</strong>.</li>
	<li>In the <strong>Confirmation OTP</strong> modal, enter the requester’s code in <strong>Requester OTP</strong>.</li>
	<li>Click <strong>Confirm</strong>.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/mi-otp-confirm.png',
	'caption' => 'Confirmation OTP modal — enter the code from the requester’s email, then Confirm.',
])

<p>
	On success, Material Issuance status becomes <strong>Completed</strong>.
	Stock is deducted from the store.
	<strong>Reverse Material Issuance</strong> may appear for authorised users if an issuance must be undone.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/mi-completed.png',
	'caption' => 'Material Issuance Completed — stock has been issued; Reverse Material Issuance available where permitted.',
])

<h2>Inventory Movement (audit trail)</h2>
<p>
	After issuance completes, open <strong>Inventory Management → Inventory Movement</strong>
	to see stock-in and stock-out history for the item.
	Filter by cost centre, category, and date range, then click <strong>Filter</strong>.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/rts-inventory-movement.png',
	'caption' => 'Inventory Movement — stock-out lines for issued items (store, slot, quantities, and linked request).',
])

<p>
	Typical columns include date, item code, stock in / stock out, UoM, cost centre, store, slot, and the related request reference.
	Use this screen to confirm that the issued quantity left the correct store and slot.
</p>

<h2>Status summary</h2>
<table class="table table-bordered table-sm">
	<thead>
		<tr>
			<th>Document</th>
			<th>Status</th>
			<th>Meaning</th>
			<th>Who acts next</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>Request to Store</td>
			<td><em>In Preparation</em></td>
			<td>Draft; not yet sent for approval</td>
			<td>Requester — complete and click Get Approval</td>
		</tr>
		<tr>
			<td>Request to Store</td>
			<td><em>Awaiting Approval</em></td>
			<td>With approver(s)</td>
			<td>Approver — Approve, Reject, or Return</td>
		</tr>
		<tr>
			<td>Request to Store</td>
			<td><em>Approval Complete</em></td>
			<td>All approvals granted</td>
			<td>Store — Create Material Issuance</td>
		</tr>
		<tr>
			<td>Material Issuance</td>
			<td><em>Awaiting User Reception</em></td>
			<td>Items ready; OTP sent to requester</td>
			<td>Store — Issue Items with OTP</td>
		</tr>
		<tr>
			<td>Material Issuance</td>
			<td><em>Completed</em></td>
			<td>Pickup confirmed; stock deducted</td>
			<td>—</td>
		</tr>
	</tbody>
</table>

<h2>Tips</h2>
<ul>
	<li>Check <strong>Open Quantity</strong> before requesting — if it is zero, use Purchase Request instead.</li>
	<li>You can create more than one Material Issuance from the same Request to Store when pending quantity remains (partial issues).</li>
	<li>Ask the requester to keep the OTP email handy before they come to collect.</li>
	<li>Use <strong>Inventory Movement</strong> after completion if you need to prove where stock went.</li>
</ul>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-clipboard-check-outline"></i></span>
	<p>
		For buying from suppliers, use the
		<a href="{{ route('usermanual.show', ['manual' => 'inventory', 'chapter' => 'request-to-order']) }}">Request to order</a>
		chapters. This chapter covers stock that is already in the store.
	</p>
</div>
