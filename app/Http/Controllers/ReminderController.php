<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\MailController as Mailer;

class ReminderController extends Controller
{
  public function send_quotes_reminders($rfq_ids){
		if(count($rfq_ids) == 0){
			return redirect()->back()->with('error', 'All Suppliers have submitted their quotes.');
		}

		foreach($rfq_ids as $id){
			$suppliers = \App\SupplierRFQ::join('suppliers as s', 's.id', 'supplier_r_f_q_s.supplier_id')
				->where('request_id', $id)->where('quote_received', '!=', 0)->selectRaw('s.name, s.email')->get();

			$rfq = \App\RequestEntity::find($id);
			$companyDetails = getCompanyDetails();

			foreach($suppliers as $supplier){
				$body = 'Hi '.$supplier->name.',<br><br>
					This is a reminder to submit your quotes for '.$rfq->request_code.'. The deadline for your submission is '.$rfq->submission_deadline.'
					<br>Regards,<br>
					'.$companyDetails['name'].'<br><br>
					<center style="color: red">***Please ignore this message if you have already submitted your quotes.</center>
					';

				$mailData = array(
					'contacts' => array($supplier->email),
					'body' => $body,
					'subject' => '[Quotes Reminder] Send your quotes for '.$rfq->request_code
				);

				$mailer = new Mailer;

				$sendMail = $mailer->html_email($mailData, 'default');
			}
		}
		return redirect()->back()->with('success', 'Quotes reminders have been sent out.');
	}

	public function get_notifiable_entities($send_reminder=0, $type, $days=3, $entity_id=false){
		if($send_reminder==1){
			$entities = \App\RequestEntity::where('request_type', $type)->where('created_at', '<=', Carbon::now()->subDays($days)->toDateTimeString());
		}
		else{
			$entities = \App\RequestEntity::where('request_type', $type)->where('created_at', '<=', Carbon::now()->subDays($days)->toDateTimeString());
		}

		$entities = $entities->where('quotes_reminder_sent', $send_reminder);

		if($entity_id){
			$entities = $entities->where('id', $entity_id);
		}

		$entities = $entities->pluck('id')->toArray();

		// return response()->json($entities, 200);

		if($type == 'Request for Quotation'){
			return $this->send_quotes_reminders($entities);
		}
		return true;
	}
}
