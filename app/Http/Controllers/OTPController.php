<?php

namespace App\Http\Controllers;

use App\OTP;
use Illuminate\Http\Request;

class OTPController extends Controller
{
	public function create($data){
		$code = rand(100000,999999);

		$otp = new OTP;
		$otp->code = $code;
		$otp->user_id = $data->user_id;
		$otp->model = $data->model;
		$otp->model_id = $data->model_id;

		$otp->save();

		return $otp;
	}

	public function close($data){
		$otp = OTP::where('code', $data->code)->where('model', $data->model)
		->where('model_id', $data->model_id)->first();

		if(!isset($otp->id)){
			return redirect()->back()->with('error', 'No matching otp found. Please confirm the code again.');
		}

		$otp->approved_by = $data->approved_by;
		$otp->save();

		return $otp;
	}
}
