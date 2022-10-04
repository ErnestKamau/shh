<?php

namespace App\Http\Controllers\Mpesa;

use App\Http\Controllers\Controller;
use App\Models\Mpesa\ConfirmationRegisterUrl;
use Illuminate\Http\Request;

class MpesaController extends Controller
{
     /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function registerMpesaConfirmationURL()
    {
        return view('prp::index');
    }

    private function getAuthorizationAccessToken()
    {
        $ch = curl_init('https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');

        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . base64_encode(env('MPESA_CONSUMER_KEY') . ':' . env('MPESA_CONSUMER_SECRET'))]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response['access_token'];
    }

    public function registerConfirmationValidationURL()
    {
        $token = $this->getAuthorizationAccessToken();

        $ch = curl_init('https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');

        curl_setopt($ch, CURLOPT_HTTPHEADER, [

            'Authorization: Bearer '.$token,

            'Content-Type: application/json'

        ]);

        curl_setopt($ch, CURLOPT_POST, 1);

        $payload = [
            "ShortCode" => "600981",
            "ResponseType" => "Completed",
            "ConfirmationURL" => "https://aqualytic.imaralims.com/get/confirmation/Callback-Url/Payload-wertyasdfgh",
            "ValidationURL" => "https://aqualytic.imaralims.com/get/Validation/Callback-Url/payload-ghfjdks"
        ];

        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response     = curl_exec($ch);

        curl_close($ch);

        return response()->json($response);
    }

    public function mpesaConfirmationCallbackUrl(Request $request)
    {
        ConfirmationRegisterUrl::insert(['trans_type' => 'Confirmation', 'body' => json_encode($request->all())]);
        return response()->json('done');
    }
    public function mpesaValidationCallbackUrl(Request $request)
    {
        ConfirmationRegisterUrl::insert(['trans_type' => 'Validation', 'body' => json_encode($request->all())]);
        return response()->json('done');
    }
    public function displayMpesaValidation(){
        $data = ConfirmationRegisterUrl::all();
        return response()->json($data);
    }
}
