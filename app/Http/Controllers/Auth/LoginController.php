<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\MailController as Mailers;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

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

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
			$this->middleware('guest')->except('logout');
    }
    public function authenticated(Request $request, $user){
			$user->generateTwoFactorCode();
            $app_name = env('APP_NAME', 'AQUALYTIC LIMS');
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
			
			$sendmail = $mailer->html_email($mailData,'default');
			
    }
}
