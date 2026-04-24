<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\MailController as Mailers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactor extends Controller
{
	public function __construct(){
		$this->middleware('auth', ['except'=>['storeVerifyCodeExt']]);
	}

	public function index(){
		return view('auth.twofactor');
	}

	public function resendVerifyCode(Request $request){
		$user = auth()->user();
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
		return redirect()->back()->with('success','Verification code sent successfully!');
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
        if($request->verify_code == $user->verify_code){
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
        return redirect()->back()->with('error','Your verification did not match the one on your email!');
	}
	public function mylogout(Request $request){
		$user = auth()->user();
		$user->is_online = 0;
		$user->save();
		Auth::logout();
		session()->flush();
		return redirect('/login');
	}
}
