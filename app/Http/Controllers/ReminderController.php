<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\MailController as Mailer;
use App\EntityApproval;
use App\RequestEntity;
use App\User;

class ReminderController extends Controller
{
	public function send_approval_reminders($id = null)
	{
		if (! $id) {
			return redirect()->back()->with('error', 'Request id is required.');
		}

		$req = RequestEntity::find($id);
		if (! $req) {
			return redirect()->back()->with('error', 'Request not found.');
		}

		$pendingApprovals = EntityApproval::query()
			->where('model_id', $req->id)
			->where('model', $req->request_type)
			->where(function ($query) {
				$query->whereNull('approved_at')
					->orWhere('status', 'Pending');
			})
			->where(function ($query) {
				$query->where('is_current', 1)
					->orWhereNull('is_current');
			})
			->get();

		if ($pendingApprovals->isEmpty()) {
			return redirect()->back()->with('error', 'No pending approvals found to remind.');
		}

		$companyDetails = getCompanyDetails();
		$mailer = new Mailer;
		$sent = 0;
		$failed = 0;
		$requisitionController = new RequisitionController;

		foreach ($pendingApprovals as $approval) {
			$user = User::find($approval->user_id);
			if (! $user || blank($user->email)) {
				$failed++;
				continue;
			}

			$approvalMoreInfo = $requisitionController->approvalMoreInfo($req, $approval);

			$body = '
				Hi '.$user->name.',<br>
				This is a reminder that there is a pending approval request for '.$req->request_type.' - '.$req->request_code.'. Please find the details below<br>
				'.$approvalMoreInfo.'<br>
				Click this link
				<a href="'.route('view-request-details', ['stage' => $req->request_type, 'id' => $req->id]).'">'.route('view-request-details', ['stage' => $req->request_type, 'id' => $req->id]).'</a> to view the request.
				<br>Regards,<br>
				'.$companyDetails['name'].'
			';

			$mailData = [
				'contacts' => [$user->email],
				'body' => $body,
				'subject' => '[Approval Reminder] Approval Request for '.$req->request_type.' - '.$req->request_code,
			];

			if ($mailer->html_email($mailData, 'default') === false) {
				$failed++;
			} else {
				$sent++;
			}
		}

		if ($sent === 0) {
			return redirect()->back()->with('error', 'Could not send approval reminder emails. Please check mail configuration.');
		}

		$message = $sent.' approval reminder'.($sent === 1 ? '' : 's').' sent.';
		if ($failed > 0) {
			$message .= ' '.$failed.' could not be delivered.';
		}

		return redirect()->back()->with($failed > 0 ? 'warning' : 'success', $message);
	}

	public function send_quotes_reminders($rfq_ids)
	{
		if (count($rfq_ids) == 0) {
			return redirect()->back()->with('error', 'All Suppliers have submitted their quotes.');
		}

		foreach ($rfq_ids as $id) {
			$suppliers = \App\SupplierRFQ::join('suppliers as s', 's.id', 'supplier_r_f_q_s.supplier_id')
				->where('request_id', $id)->where('quote_received', '!=', 0)->selectRaw('s.name, s.email')->get();

			$rfq = \App\RequestEntity::find($id);
			$companyDetails = getCompanyDetails();

			foreach ($suppliers as $supplier) {
				$body = 'Hi '.$supplier->name.',<br><br>
					This is a reminder to submit your quotes for '.$rfq->request_code.'. The deadline for your submission is '.$rfq->submission_deadline.'
					<br>Regards,<br>
					'.$companyDetails['name'].'<br><br>
					<center style="color: red">***Please ignore this message if you have already submitted your quotes.</center>
					';

				$mailData = [
					'contacts' => [$supplier->email],
					'body' => $body,
					'subject' => '[Quotes Reminder] Send your quotes for '.$rfq->request_code,
				];

				$mailer = new Mailer;

				$mailer->html_email($mailData, 'default');
			}
		}

		return redirect()->back()->with('success', 'Quotes reminders have been sent out.');
	}

	public function get_notifiable_entities($send_reminder = 0, $type, $days = 3, $entity_id = false)
	{
		$entities = \App\RequestEntity::where('request_type', $type)
			->where('created_at', '<=', Carbon::now()->subDays($days)->toDateTimeString())
			->where('quotes_reminder_sent', $send_reminder);

		if ($entity_id) {
			$entities = $entities->where('id', $entity_id);
		}

		$entities = $entities->pluck('id')->toArray();

		if ($type == 'Request for Quotation') {
			return $this->send_quotes_reminders($entities);
		}

		return true;
	}
}
