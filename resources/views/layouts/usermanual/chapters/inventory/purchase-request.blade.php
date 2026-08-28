<h2>What is a Purchase Request?</h2>
<p>
	A <strong>Purchase Request (PR)</strong> is how a department asks the organisation to buy items.
	You describe what is needed, add line items with quantities, and send the request through approval.
	Once approved, Procurement turns it into a <strong>Request for Quotation (RFQ)</strong> so suppliers can be contacted.
</p>
<p>
	Open <strong>Inventory Management → Request to Order → Purchase Request</strong>.
</p>

<h2>The Purchase Request list</h2>
<p>
	Open <strong>Inventory Management → Request to Order → Purchase Request</strong>.
	The main screen has three tabs at the top: <strong>Requests</strong>, <strong>Completed</strong>, and <strong>Approval Configuration</strong>.
	Use <strong>+ Add</strong> (top right) to create a new Purchase Request.
</p>

<h3>Requests tab — column by column</h3>
<ul>
	<li><strong>#</strong> — row number in the table.</li>
	<li><strong>Checkbox</strong> — select one or more rows for bulk actions (where available).</li>
	<li><strong>Indicator</strong> — coloured bar on the left showing status at a glance (for example green when approval is complete, yellow when awaiting approval).</li>
	<li><strong>Code</strong> — the PR reference (for example <em>PR20260007</em>). Click to open the full record.</li>
	<li><strong>Items</strong> — a summary of the item names on the request.</li>
	<li><strong>Priority</strong> — urgency level (for example Normal, Urgent).</li>
	<li><strong>Status</strong> — where the PR is in its lifecycle (for example <em>In Preparation</em>, <em>Awaiting Approval</em>, <em>Approval Complete</em>).</li>
	<li><strong>Description</strong> — the purpose text entered on the General tab.</li>
	<li><strong>Due Date</strong> — the latest delivery date from the item lines.</li>
	<li><strong>Created By</strong> — who raised the request.</li>
	<li><strong>Department</strong> — the requester’s department.</li>
	<li><strong>Created On</strong> — when the PR was first saved.</li>
</ul>
<p>
	Use the <strong>Search</strong> box and <strong>Show entries</strong> dropdown to filter a long list.
	Export buttons (Copy, CSV, Excel, PDF, Print) let you download the table for reporting.
</p>

<h3>Completed tab</h3>
<p>
	Shows Purchase Requests that have finished their life on this stage — for example after Procurement has created an RFQ and the PR is marked complete.
	Use this tab to look up historical requests without scrolling through active ones.
</p>

<h2>Approval Configuration</h2>
<p>
	Before anyone can approve Purchase Requests, an administrator sets up <strong>who is allowed to approve</strong>.
	Open the <strong>Approval Configuration</strong> tab on the Purchase Request list.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/pr-approval-config.png',
	'caption' => 'Approval Configuration tab — define approval steps and assign a role to each step.',
])

<h3>What you see on Approval Configuration</h3>
<ul>
	<li><strong>Title</strong> — a label for this approval step (for example <em>PR Approver</em>). Shown on the Approvals tab of each PR.</li>
	<li><strong>Users</strong> — everyone currently assigned to the linked role (name and email). Any of these users can act when the request reaches their step.</li>
	<li><strong>Edit (pencil)</strong> — change the title, role, or approval level for an existing step.</li>
	<li><strong>+ (top right)</strong> — add a new approval step.</li>
</ul>

<h3>Adding a new approval step</h3>
<p>Click <strong>+</strong> to open <strong>Add New Approval</strong>:</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/pr-approval-config-add.png',
	'caption' => 'Add New Approval — enter a title and select the approver role.',
])

<ul>
	<li><strong>Approval Title</strong> — a clear name for this step (for example <em>Department Head</em> or <em>Inventory Manager Approval</em>).</li>
	<li><strong>Select Role</strong> — pick the system role whose members may approve at this step (for example <em>Inventory Manager Group</em>). Only users in that role will appear as approvers.</li>
</ul>
<p>
	If you need more than one approval in sequence, add multiple steps.
	Each step is processed in order — the next approver only sees action buttons after the previous step is approved.
</p>
<p>
	<strong>Who sets this up:</strong> typically an Inventory administrator or manager with access to Approval Configuration.
	Department staff who only create PRs do not need to change these settings.
</p>

<h2>Creating a Purchase Request</h2>
<p>
	Click <strong>+ Add</strong> on the Purchase Request list.
	You land on <strong>Create Purchase Request</strong> with status <strong>In Preparation</strong>.
	The record has six tabs: <strong>General</strong>, <strong>Items</strong>, <strong>Approvals</strong>, <strong>Notes</strong>, <strong>Attachments</strong>, and <strong>Requisition Flow</strong>.
</p>
<p>
	Click <strong>Save</strong> (top right) whenever you change something.
	Many fields auto-save as you move between tabs, but always save before leaving if you are unsure.
</p>

<h2>General tab</h2>
<p>
	The General tab holds the header information for the whole request.
	Required fields are marked with a red asterisk (*).
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/pr-create-general.png',
	'caption' => 'General tab — description, department, cost centre, and priority.',
])

<h3>Left column</h3>
<ul>
	<li><strong>Description/Purpose</strong> — explain why the purchase is needed. Approvers and Procurement read this first; be specific (project name, lab area, reason for urgency).</li>
	<li><strong>Company Unit</strong> — your current location (for example <em>Dubai Lab</em>). Filled automatically from your login; you cannot change it here.</li>
	<li><strong>Department</strong> — your department. Filled automatically from your user profile.</li>
	<li><strong>Cost Center*</strong> — which budget or cost centre will be charged. You can select one or more from the dropdown. Required before approval.</li>
	<li><strong>Nature of Purchase*</strong> — classifies the spend (for example Normal, Capex). If you choose <em>Capex</em>, a <strong>CAPEX Project Number</strong> field appears and must be filled in.</li>
</ul>

<h3>Right column</h3>
<ul>
	<li><strong>Requested By</strong> — the person asking for the goods (usually you). Set automatically.</li>
	<li><strong>Created On</strong> — date and time the PR was first saved. Empty until the first save.</li>
	<li><strong>Created By</strong> — who created the record in the system.</li>
	<li><strong>Updated On</strong> — last time any field was changed.</li>
	<li><strong>Priority*</strong> — how urgent the request is (for example <em>Normal</em>). Choose a higher priority only when delivery timing is critical.</li>
	<li><strong>Status</strong> — read-only. Starts as <em>In Preparation</em> until you send for approval.</li>
</ul>

<p>
	<strong>Role:</strong> the <strong>requester</strong> (department staff) fills in General and Items, then saves.
</p>

<h2>Items tab</h2>
<p>
	Every product you want to buy is a line on the Items tab.
	A Purchase Request must have at least one item line before you can send it for approval.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/pr-create-items-empty.png',
	'caption' => 'Items tab — empty grid ready for the first line.',
])

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/pr-create-items-filled.png',
	'caption' => 'Items tab — one line filled in with item, quantity, and delivery date.',
])

<h3>Adding and removing lines</h3>
<ul>
	<li><strong>+ Add</strong> — inserts a new blank row at the bottom.</li>
	<li><strong>Remove</strong> — deletes the rows you have ticked in the checkbox column.</li>
</ul>

<h3>Columns on each line</h3>
<ul>
	<li><strong>#</strong> — line number.</li>
	<li><strong>Item</strong> — search and select from the inventory catalogue (<em>Please Select Inventory Item…</em>). Picking from the catalogue keeps names and codes consistent.</li>
	<li><strong>Brand</strong> — optional preferred manufacturer or brand.</li>
	<li><strong>Description/Location</strong> — extra detail (for example <em>FOR LAB USE</em>) or where the goods should be delivered. The map-pin icon may open a location picker where configured.</li>
	<li><strong>UoM</strong> — unit of measure (for example Boxes, Litres). Options come from the item’s configured units.</li>
	<li><strong>Open Quantity</strong> — current stock or open quantity for reference (read-only). Helps you see whether stock already exists.</li>
	<li><strong>Requested Quantity</strong> — how many units you need. Must be within the item’s maximum order quantity if one is set.</li>
	<li><strong>Delivery Date</strong> — when you need the goods by. The PR’s <strong>Due Date</strong> on the list is taken from the latest delivery date on the lines.</li>
</ul>
<p>
	While status is <em>In Preparation</em>, you can edit all item fields.
	After you send for approval, item lines become read-only unless the request is returned to you.
</p>
<p>
	<strong>Role:</strong> the <strong>requester</strong> adds and checks item lines.
</p>

<h2>Approvals tab</h2>
<p>
	Before you send the PR for approval, this tab may be empty or show configured steps as <em>Not Sent</em>.
	After you click <strong>Get Approval</strong>, each configured step appears with its status.
</p>
<p>Columns on the Approvals tab:</p>
<ul>
	<li><strong>Approval</strong> — the step title from Approval Configuration, plus the role name underneath.</li>
	<li><strong>Created At</strong> — when this approval step was created for this PR.</li>
	<li><strong>Approved By</strong> — the user assigned to act (with a <strong>Change</strong> link while the request is awaiting approval, if your role allows reassignment).</li>
	<li><strong>Approved At</strong> — date and time of approval, or blank while pending.</li>
	<li><strong>Status</strong> — action buttons for the current approver: <strong>Approve</strong>, <strong>Reject</strong>, or <strong>Return</strong>.</li>
</ul>
<p>
	<strong>Send Reminder</strong> (top right) emails approvers who have not yet acted.
</p>
<p>
	<strong>Roles:</strong>
</p>
<ul>
	<li><strong>Requester</strong> — opens this tab to track progress; uses Send Reminder if needed.</li>
	<li><strong>Approver</strong> — uses Approve, Reject, or Return when the PR is <em>Awaiting Approval</em> and they are the assigned user.</li>
</ul>

<h2>Notes tab</h2>
<p>
	Add internal comments that stay on the record for audit and handover.
</p>
<ul>
	<li><strong>+ Note</strong> — add a new note (choose note type where prompted).</li>
	<li><strong>Remove</strong> — delete selected notes.</li>
	<li>Columns: <strong>Note Type</strong>, <strong>Created By</strong>, <strong>Created On</strong>.</li>
</ul>
<p>
	Use Notes for instructions to Procurement (“use approved vendor only”) or explanations after a Return from an approver.
</p>

<h2>Attachments tab</h2>
<p>
	Upload supporting files — quotes, specifications, photos, or emails.
</p>
<ul>
	<li><strong>+ Attachment</strong> — upload a file and give it a title and type.</li>
	<li><strong>Remove</strong> — delete selected attachments.</li>
	<li>Columns: <strong>File</strong>, <strong>Title</strong>, <strong>Type</strong>, <strong>Created By</strong>, <strong>Created On</strong>.</li>
</ul>
<p>
	Attachments are optional but help approvers and buyers justify the purchase.
</p>

<h2>Requisition Flow tab</h2>
<p>
	Shows where this Purchase Request sits in the full buying chain: <strong>Purchase Request → Request for Quotation → Purchase Orders → Goods Receipt</strong>.
	Each column lists documents at that stage linked to this request.
</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/pr-flow-in-preparation.png',
	'caption' => 'Requisition Flow — PR is active; later stages show “Not yet at this stage.”',
])

<p>On each document card you will see:</p>
<ul>
	<li><strong>Code and status</strong> — for example <em>PR20260012 — In Preparation</em>.</li>
	<li><strong>Description</strong> — copied from the PR purpose text.</li>
	<li><strong>Source</strong> — link to the parent document (for RFQ/PO/GR created from this PR).</li>
	<li><strong>View</strong> — open that document.</li>
</ul>
<p>
	While the PR is still in preparation, RFQ, PO, and Goods Receipt columns show <em>Not yet at this stage.</em>
	As Procurement and Store progress the order, new cards appear in the later columns.
</p>

<h2>End-to-end process and roles</h2>
<p>
	Below is the full Purchase Request lifecycle from draft to handover to Procurement.
</p>

<h3>Step 1 — Draft (In Preparation)</h3>
<p><strong>Who:</strong> Requester (department staff).</p>
<ol>
	<li>Create a new PR with <strong>+ Add</strong>.</li>
	<li>Fill <strong>General</strong> (description, cost centre, nature of purchase, priority).</li>
	<li>Add lines on <strong>Items</strong> with quantities and delivery dates.</li>
	<li>Add <strong>Notes</strong> or <strong>Attachments</strong> if needed.</li>
	<li>Click <strong>Save</strong>. Status stays <em>In Preparation</em> until you are ready.</li>
</ol>

<h3>Step 2 — Send for approval</h3>
<p><strong>Who:</strong> Requester.</p>
<ol>
	<li>Confirm at least one item line exists and required General fields are filled.</li>
	<li>Click <strong>Get Approval</strong> (top right).</li>
	<li>Status changes to <em>Awaiting Approval</em>. Approvers are notified.</li>
</ol>

<h3>Step 3 — Approval in progress</h3>
<p><strong>Who:</strong> Approver (users in the role configured under Approval Configuration).</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/pr-approvals-awaiting.png',
	'caption' => 'Approvals tab — approver can Approve, Reject, or Return the request.',
])

<ol>
	<li>Open the PR (from the list, Approval Requests menu, or email link).</li>
	<li>Review <strong>General</strong>, <strong>Items</strong>, <strong>Notes</strong>, and <strong>Attachments</strong>.</li>
	<li>On the <strong>Approvals</strong> tab, choose one action:
		<ul>
			<li><strong>Approve</strong> — accept; if this was the last required step, status becomes <em>Approval Complete</em>.</li>
			<li><strong>Reject</strong> — decline the request; it does not proceed.</li>
			<li><strong>Return</strong> — send back to the requester to fix details; status returns to <em>In Preparation</em> so they can edit and resubmit.</li>
		</ul>
	</li>
</ol>
<p>
	If multiple approval steps exist, they run in order — step 2 only becomes active after step 1 is approved.
</p>

<h3>Step 4 — Approval complete</h3>
<p><strong>Who:</strong> Requester (read-only); Procurement (next action).</p>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/pr-approval-complete.png',
	'caption' => 'Approval Complete — General tab with Send to Procurement (create RFQ) button.',
])

<p>
	When every required approver has approved, status shows <strong>Approval Complete</strong>.
	The requester can no longer edit items.
	Procurement receives notification that a PR is ready.
</p>

<h3>Step 5 — Send to Procurement (create RFQ)</h3>
<p><strong>Who:</strong> Procurement (users in the Inventory Procurement Group or equivalent role).</p>
<ol>
	<li>Open the approved Purchase Request.</li>
	<li>Click <strong>Send to Procurement (create RFQ)</strong> (green button, top right).</li>
	<li>The system creates a new <strong>Request for Quotation</strong> with the same items and general details.</li>
</ol>

@include('layouts.usermanual.partials.figure', [
	'src' => '/images/usermanual/inventory/pr-rfq-created.png',
	'caption' => 'RFQ created from the approved PR — success message and new RFQ in In Preparation.',
])

<p>
	You are taken to the new RFQ (for example <em>RFQ20260008</em>) in <em>In Preparation</em> status.
	From here, continue in the <strong>Request for Quotation</strong> chapter — add suppliers, send quotes, and award.
</p>
<p>
	On <strong>Requisition Flow</strong>, the RFQ now appears in the second column with a <strong>Source</strong> link back to the original PR.
</p>

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
			<td><em>In Preparation</em></td>
			<td>Draft; not yet sent for approval</td>
			<td>Requester — complete and click Get Approval</td>
		</tr>
		<tr>
			<td><em>Awaiting Approval</em></td>
			<td>With approver(s)</td>
			<td>Approver — Approve, Reject, or Return</td>
		</tr>
		<tr>
			<td><em>Partially Approved</em></td>
			<td>Some approval steps done, others pending</td>
			<td>Next approver in sequence</td>
		</tr>
		<tr>
			<td><em>Approval Complete</em></td>
			<td>All approvals granted</td>
			<td>Procurement — Send to Procurement (create RFQ)</td>
		</tr>
	</tbody>
</table>

<h2>Tips for requesters</h2>
<ul>
	<li>Select items from the catalogue so descriptions and units stay correct.</li>
	<li>Write a clear <strong>Description/Purpose</strong> — approvers often decide based on this alone.</li>
	<li>Check <strong>Open Quantity</strong> — you may already have stock or an open order.</li>
	<li>Attach quotes or specs on the <strong>Attachments</strong> tab when they exist.</li>
	<li>Lab users may see an extra <strong>Lab Kits</strong> tab on the list for cloning frequent kit requests.</li>
</ul>

<div class="um-tip">
	<span class="um-tip__icon"><i class="mdi mdi-clipboard-check-outline"></i></span>
	<p>
		After Procurement creates the RFQ, your work on the Purchase Request is done.
		Follow progress on <strong>Requisition Flow</strong> or open the RFQ directly from the Request for Quotation menu.
	</p>
</div>
