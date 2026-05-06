<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\MailController as Mailers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

class TwoFactor extends Controller
{
	private const OTP_VERIFY_MAX_ATTEMPTS = 5;
	private const OTP_VERIFY_DECAY_SECONDS = 300;
	private const OTP_RESEND_MAX_ATTEMPTS_BEFORE_LOGOUT = 5;
	private const OTP_RESEND_DELAY_SECONDS_BY_ATTEMPT = [
		3 => 300,
		4 => 600,
		5 => 900,
	];

	public function __construct(){
		$this->middleware('auth', ['except'=>['storeVerifyCodeExt']]);
	}

	public function index(){
		$user = auth()->user();
		$resendState = $this->getResendState((string) $user->id);
		$waitSeconds = max(0, ((int) ($resendState['available_at'] ?? 0)) - time());

		return view('auth.twofactor', [
			'otpAttemptsRemaining' => session('otp_attempts_remaining'),
			'otpRetryAfterSeconds' => session('otp_retry_after_seconds'),
			'resendWaitSeconds' => session('resend_wait_seconds', $waitSeconds),
			'resendAttemptsUsed' => (int) ($resendState['count'] ?? 0),
		]);
	}

	public function resendVerifyCode(Request $request){
		$user = auth()->user();
		$state = $this->getResendState((string) $user->id);
		$now = time();

		if ((int) $state['count'] >= self::OTP_RESEND_MAX_ATTEMPTS_BEFORE_LOGOUT) {
			$user->is_online = 0;
			$user->save();
			Auth::logout();
			session()->flush();

			return redirect()->route('login')->with('error', 'Too many OTP resend requests. Please login again.');
		}

		$remainingWait = max(0, ((int) ($state['available_at'] ?? 0)) - $now);
		if ($remainingWait > 0) {
			return redirect()->back()
				->with('error', 'You can resend OTP after '.$this->formatWait($remainingWait).'.')
				->with('resend_wait_seconds', $remainingWait);
		}

		$nextCount = (int) $state['count'] + 1;
		$nextDelay = (int) (self::OTP_RESEND_DELAY_SECONDS_BY_ATTEMPT[$nextCount] ?? 0);

		try {
			$user->generateTwoFactorCode();
			$app_name = env('APP_NAME', 'FIVET LIMS');
			$body = 'Hi '.$user->first_name.',<br><br>
				Your verification code has been successfully generated. Your verification code is:
				<br><br>'.$user->verify_code ;
			$mailData = array(
				'contacts'=>array($user->email),
				'body'=>$body,
				'subject'=>'['.$app_name.'] Verification Code -'.$user->email
			);
			$mailer = new Mailers;
			$sendmail = $mailer->html_email($mailData,'default');
			if ($sendmail === false) {
				return redirect()->back()->with('error', 'Verification code generated, but email could not be sent. Please verify mail settings or try again.');
			}

			$this->storeResendState((string) $user->id, [
				'count' => $nextCount,
				'available_at' => $nextDelay > 0 ? ($now + $nextDelay) : 0,
			]);

			return redirect()->back()
				->with('success', 'Verification code sent successfully!')
				->with('resend_wait_seconds', $nextDelay);
		} catch (Throwable $exception) {
			report($exception);

			return redirect()->back()->with('error', 'Verification code generated, but email verification failed. Please try again.');
		}
	}

	public function storeVerifyCodeExt(Request $request){
		$verification_key = $request->verification_key;

		$company = \App\Company::where('client_number', $request->client_id)->first();
		$company->license_key = $verification_key;
		$company->license_expiry = $request->expiry;
		$company->save();

		$users = \App\User::where('company_id', $company->id)
			->selectRaw('license_type, count(id) as total')->groupBy('license_type')->get();

		$userArr = array();

		foreach($users as $u){
			if(!isset($userArr[$u->license_type])){
				$userArr[$u->license_type] = 0;
			}
			$userArr[$u->license_type] = $u->total;
		}

		$response = array(
			"status" => true,
			"message" =>$userArr
		);

		return json_encode($response);
		// try{
		// 	$verification_key = $request->verification_key;
		// 	$company = \App\Company::where('client_number', $request->client_id)->first();
		// 	$company->license_key = $verification_key;
		// 	$company->license_expiry = $request->expiry;
		// 	$company->save();

		// 	$users = \App\User::where('company_id', $company->id)
		// 		->selectRaw('license_type, count(id) as total')->groupBy('license_type')->get();

		// 	$userArr = array();

		// 	foreach($users as $u){
		// 		if(!isset($userArr[$u->license_type])){
		// 			$userArr[$u->license_type] = 0;
		// 		}
		// 		$userArr[$u->license_type] = $u->total;
		// 	}

		// 	return json_encode(array(
		// 		"status" => true,
		// 		"message" =>$userArr
		// 	));
		// }
		// catch(Exception $exception){
		// 	return json_encode(array(
		// 		"status" => false,
		// 		"message" =>$exception->errorMessage()
		// 	));
		// }
	}

	public function storeVerifyCode(Request $request){
        $request->validate([
            'verify_code'=> 'integer|required',
        ]);

        $user = auth()->user();
		$rateKey = 'otp-verify:' . (string) $user->id;

		if (RateLimiter::tooManyAttempts($rateKey, self::OTP_VERIFY_MAX_ATTEMPTS)) {
			$retryAfter = RateLimiter::availableIn($rateKey);
			return redirect()->back()
				->with('error', 'Too many invalid OTP attempts. Try again after '.$this->formatWait($retryAfter).'.')
				->with('otp_attempts_remaining', 0)
				->with('otp_retry_after_seconds', $retryAfter);
		}

        if($request->verify_code == $user->verify_code){
            RateLimiter::clear($rateKey);
			$this->clearResendState((string) $user->id);
            $user->resetTwoFactor();
            if($user->is_client == 1){
                return redirect()->route('client-dashboard-home');
            }elseif($user->supplier_id > 0){
				$user->is_online = 1;
				$user->save();
				
				return redirect()->route('supplier-dashboard-home');
			}
			$user->is_online = 1;
			$user->save();
            return redirect()->route('home');
            
        }

		RateLimiter::hit($rateKey, self::OTP_VERIFY_DECAY_SECONDS);
		$remainingAttempts = max(0, self::OTP_VERIFY_MAX_ATTEMPTS - RateLimiter::attempts($rateKey));

		return redirect()->back()
			->with('error', 'Your verification did not match the one on your email!')
			->with('otp_attempts_remaining', $remainingAttempts)
			->with('otp_retry_after_seconds', RateLimiter::availableIn($rateKey));
	}
	public function mylogout(Request $request){
		$user = auth()->user();
		$user->is_online = 0;
		$user->save();
		Auth::logout();
		session()->flush();
		return redirect('/login');
	}

	private function resendStateSessionKey(string $userId): string
	{
		return 'otp_resend_state_' . $userId;
	}

	private function getResendState(string $userId): array
	{
		$state = session($this->resendStateSessionKey($userId), ['count' => 0, 'available_at' => 0]);

		return [
			'count' => (int) ($state['count'] ?? 0),
			'available_at' => (int) ($state['available_at'] ?? 0),
		];
	}

	private function storeResendState(string $userId, array $state): void
	{
		session([$this->resendStateSessionKey($userId) => $state]);
	}

	private function clearResendState(string $userId): void
	{
		session()->forget($this->resendStateSessionKey($userId));
	}

	private function formatWait(int $seconds): string
	{
		$seconds = max(0, $seconds);
		$minutes = (int) floor($seconds / 60);
		$remainingSeconds = $seconds % 60;

		if ($minutes <= 0) {
			return $remainingSeconds . 's';
		}

		return $minutes . 'm ' . $remainingSeconds . 's';
	}
}
