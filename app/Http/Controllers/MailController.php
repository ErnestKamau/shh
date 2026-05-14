<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Throwable;

class MailController extends Controller
{
	public function html_email($data = false, $type = "report", $file = false, $bcc = false, $bcc_emails_arr = [])
	{
		$app_name = env('APP_NAME', 'FIVET LIMS');
		$mail_username = env('MAIL_USERNAME', 'kecuimara@gmail.com');

		if ($type == "report") {
			$BD = '';

			foreach ($data['batches'] as $code) {
				$batch = \App\SampleHeader::where('batch_code', $code)->first();
				$header = \App\ReportHeaderDetail::where('sample_header_id', $batch->id)->first();
				$batch->set_date("Email Date", \Carbon\Carbon::now(), true);
				$report = storage_path() . '/' . $batch->batch_report_url;
				$body = array("body" => $header->outgoing_email_body);
				$BD = $body['body'];

				try {
					Mail::send('emails.report', $body, function ($message) use ($data, $report, $mail_username, $app_name) {
						$message->to($data['contacts'])->subject('[' . $app_name . '] Your Sample Analysis Report Is Ready');
						$message->attach($report);

						$message->from($mail_username, $app_name);
					});
				} catch (Throwable $exception) {
					report($exception);
					Log::error('Failed to send report email.', [
						'contacts' => $data['contacts'] ?? null,
						'error' => $exception->getMessage(),
					]);

					return false;
				}
			}
			return $BD;
		} else {
			$bcc_emails = getBccEmails();
			if (sizeof($bcc_emails) < 0) {
				return redirect()->back()->with('error', 'Kindly set up the BCC emails.');
			}
			try {
				Mail::send('emails.notification', $data, function ($message) use ($data, $file, $bcc, $bcc_emails, $mail_username, $app_name, $bcc_emails_arr) {
					$message->to($data['contacts'])->subject($data['subject']);
					if ($bcc == true) {
						$message->bcc($bcc_emails);
					}
					if (sizeof($bcc_emails_arr) > 0) {
						$message->bcc($bcc_emails_arr);
					}

					try {
						$emailSent = new \App\EmailSent;
						$emailSent->email = gettype($data['contacts']) == 'array' ?  implode(",", $data['contacts']) : $data['contacts'];
						$emailSent->subject = $data['subject'];
						$emailSent->body = json_encode($data);
						$emailSent->save();
					} catch (Throwable $exception) {
						Log::warning('Email sent but failed to persist email_sents audit row.', [
							'contacts' => $data['contacts'] ?? null,
							'subject' => $data['subject'] ?? null,
							'error' => $exception->getMessage(),
						]);
					}

					if(isset($data['file'])){
						if(gettype($data['file']) == "array"){
							foreach($data['file'] as $f){
								$message->attach($f);
							}
						}
						else{
							$message->attach($data['file']);
						}
					}
					if($file){
						if(is_array($file)){
							foreach($file as $f){
								$message->attach($f);
							}
						}
						else{
							$message->attach($file);
						}
					}

					$message->from($mail_username, $app_name);
				});
			} catch (Throwable $exception) {
				report($exception);
				Log::error('Failed to send notification email.', [
					'contacts' => $data['contacts'] ?? null,
					'subject' => $data['subject'] ?? null,
					'error' => $exception->getMessage(),
				]);

				return false;
			}
			// Log::info('Mail sent to '.implode(",", $data['contacts']));
		}
	}
}
