<?php

namespace App\Http\Controllers;

use App\OTP;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OTPController extends Controller
{
	public function create($data)
	{
		$code = rand(100000, 999999);

		$otp = new OTP;
		$otp->code = $code;
		$otp->user_id = $data->user_id;
		$otp->model = $data->model;
		$otp->model_id = $data->model_id;

		$otp->save();

		return $otp;
	}

	/**
	 * Deliver an OTP on first send via email and SMS (whichever contacts are available).
	 */
	public function notifyUser(
		OTP $otp,
		?User $user,
		string $emailSubject,
		string $emailBody,
		string $smsMessage
	): void {
		if (! $user) {
			Log::warning('OTP notify skipped: user missing.', [
				'otp_id' => $otp->id,
				'model' => $otp->model,
				'model_id' => $otp->model_id,
			]);

			return;
		}

		$email = trim((string) ($user->email ?? ''));
		if ($email !== '') {
			$mailer = new MailController;
			$mailer->html_email([
				'contacts' => [$email],
				'body' => $emailBody,
				'subject' => $emailSubject,
				'eyebrow' => 'Confirmation OTP',
				'heading' => $emailSubject,
				'pageTitle' => $emailSubject,
			], 'default');
		} else {
			Log::warning('OTP email skipped: user has no email.', [
				'otp_id' => $otp->id,
				'user_id' => $user->id,
			]);
		}

		$phone = trim((string) ($user->phone ?? ''));
		if ($phone !== '') {
			sendTextMessage($phone, $smsMessage);
		} else {
			Log::info('OTP SMS skipped: user has no phone.', [
				'otp_id' => $otp->id,
				'user_id' => $user->id,
			]);
		}
	}

	public function close($data)
	{
		$otp = OTP::where('code', $data->code)->where('model', $data->model)
			->where('model_id', $data->model_id)->first();

		if (! isset($otp->id)) {
			return redirect()->back()->with('error', 'No matching otp found. Please confirm the code again.');
		}

		$otp->approved_by = $data->approved_by;
		$otp->save();

		return $otp;
	}
}
