<?php

namespace App\Http\Controllers;


use Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MailController extends Controller
{
  public function html_email($data = false, $type="report", $file=false, $bcc=false,$bcc_emails_arr =[]) {
	$app_name = env('APP_NAME', 'POLUCON LIMS');
	$mail_username = env('MAIL_USERNAME', 'notifications@polucon.co.ke');

		if($type == "report"){
			$BD = '';
			
			foreach($data['batches'] as $code){
				$batch = \App\SampleHeader::where('batch_code', $code)->first();
				$header = \App\ReportHeaderDetail::where('sample_header_id', $batch->id)->first();
				$batch->set_date("Email Date", \Carbon\Carbon::now(), true);
				$report = storage_path().'/'.$batch->batch_report_url;
				$body = array("body"=>$header->outgoing_email_body);
				$BD = $body['body'];
				
				
				Mail::send('emails.report', $body, function($message) use($data,$report, $mail_username, $app_name) {
					$message->to($data['contacts'])->subject('['.$app_name .'] Your Sample Analysis Report Is Ready');
					$message->attach($report);
					
					$message->from($mail_username, $app_name);
				});
			}
			return $BD;
		}
		else{
			$bcc_emails = getBccEmails();
			if(sizeof($bcc_emails)<0){
				return redirect()->back()->with('error','Kindly set up the BCC emails.');
			}
			Mail::send('emails.notification', $data, function($message) use($data, $file,$bcc,$bcc_emails, $mail_username, $app_name,$bcc_emails_arr) {
				$message->to($data['contacts'])->subject($data['subject']);
				if($bcc == true){
					$message->bcc($bcc_emails);
				}
				if(sizeof($bcc_emails_arr) > 0){
					$message->bcc($bcc_emails_arr);
				}
				if(isset($data['file'])){
					$message->attach($data['file']);
				}
				if($file){
					$message->attach($file);
				}

				$message->from($mail_username, $app_name);
			});
			// Log::info('Mail sent to '.implode(",", $data['contacts']));
		}
	}
}
