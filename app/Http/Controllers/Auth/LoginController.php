<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\MailController as Mailers;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Throwable;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use App\User;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';
    protected int $persistentLockThreshold = 5;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
			$this->middleware('guest')->except('logout');
    }

    protected function validateLogin(Request $request): void
    {
      $request->validate([
        $this->username() => ['required', 'string'],
        'password' => ['required', 'string'],
      ]);

      $user = $this->resolveUserByLogin((string) $request->input($this->username()));

      if ($user && (bool) $user->login_locked_by_admin_reset) {
        session()->put('failed_login_attempts', (int) $user->failed_login_attempts);
        session()->put('is_account_locked', true);
        throw ValidationException::withMessages([
          $this->username() => ['This account is locked. Please contact an administrator to reset it.'],
        ]);
      }
    }

    protected function sendFailedLoginResponse(Request $request)
    {
      $throttleAttempts = (int) $this->limiter()->attempts($this->throttleKey($request));
      $remainingAttempts = max(0, $this->maxAttempts() - $throttleAttempts);

      $user = $this->resolveUserByLogin((string) $request->input($this->username()));

      if ($user && ! (bool) $user->login_locked_by_admin_reset) {
        $user->failed_login_attempts = (int) $user->failed_login_attempts + 1;

        if ((int) $user->failed_login_attempts >= $this->persistentLockThreshold) {
          $user->login_locked_by_admin_reset = true;
          $remainingAttempts = 0;
        } elseif ((int) $user->failed_login_attempts === 2) {
          session()->put('login_attempts_warning', 'Warning: You have only 3 chances left. After 5 failed attempts your account will be locked.');
        }

        $user->save();
        // Pass failed attempts count to view for badge display
        session()->put('failed_login_attempts', (int) $user->failed_login_attempts);
        session()->put('is_account_locked', (bool) $user->login_locked_by_admin_reset);
      }

      if ($user && (bool) $user->login_locked_by_admin_reset) {
        $message = 'This account is locked after 5 unsuccessful login attempts. Please contact an administrator to reset it.';
        session()->put('failed_login_attempts', (int) $user->failed_login_attempts);
        session()->put('is_account_locked', true);
      } else {
        $message = $remainingAttempts > 0
          ? 'Invalid credentials. Remaining attempts: ' . $remainingAttempts . '.'
          : 'Invalid credentials. Remaining attempts: 0. Next failure will lock your account for admin reset.';
      }

      throw ValidationException::withMessages([
        $this->username() => [$message],
      ]);
    }

    protected function resolveUserByLogin(string $login): ?User
    {
      $trimmedLogin = trim($login);

      if ($trimmedLogin === '') {
        return null;
      }

      if (is_numeric($trimmedLogin)) {
        return User::where('phone', $trimmedLogin)->first();
      }

      return User::where('email', $trimmedLogin)->first();
    }

    public function authenticated(Request $request, $user){
      session()->forget('failed_login_attempts');
      session()->forget('is_account_locked');
      session()->forget('login_attempts_warning');

  			if ((int) $user->failed_login_attempts > 0 || (bool) $user->login_locked_by_admin_reset) {
  				$user->failed_login_attempts = 0;
  				$user->login_locked_by_admin_reset = false;
  				$user->save();
  			}

			// Strict 2FA: if user has authenticator enabled, require TOTP instead of email OTP.
			if ($user->two_factor_confirmed_at) {
				$user->resetTwoFactor();
				Session::put('totp_required', true);
				return;
			}

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
			
      if(trim($user->phone) != ""){
        sendTextMessage($user->phone, "Your Verification code is ".$user->verify_code);
      }

      try {
        $sendmail = $mailer->html_email($mailData,'default');
        if ($sendmail === false) {
          return redirect()->route('login')->with('error', 'Verification code generated, but email could not be sent. Please verify mail settings or try again.');
        }
      } catch (Throwable $exception) {
        report($exception);

        return redirect()->route('login')->with('error', 'Verification code generated, but email verification failed. Please try again.');
      }
			
    }
    protected function credentials(Request $request)
        {
          if(is_numeric($request->get('email'))){
            return ['phone'=>$request->get('email'),'password'=>$request->get('password')];
          }
          elseif (filter_var($request->get('email'), FILTER_VALIDATE_EMAIL)) {
            return ['email' => $request->get('email'), 'password'=>$request->get('password')];
          }
          
        }
}
