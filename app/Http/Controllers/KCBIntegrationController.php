<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KCBIntegrationController extends Controller
{
    public function receivepayment(Request $request)
    {
        $payload = $request->all();
        $header = $request->header('signature');        // Get the full path to the PEM file in the storage directory
        $publicKeyPath = storage_path('kcb_uat_publickey.pem');

        // Load the public key from the file
        $publicKey = file_get_contents($publicKeyPath);

        // Check if the public key was loaded successfully
        if (!$publicKey) {
            $message = "Failed to read the public key from the file.";
            return response()->json($message);
        }

        // Attempt to get the public key resource
        $keyResource = openssl_pkey_get_public($publicKey);

        if (!$keyResource) {
           $message = "Failed to load the public key: " . openssl_error_string();
           return response()->json($message);
        } else {
            $message = "Public key loaded successfully.";
           
        }
        $payload_encode = json_encode($payload,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        dd($payload_encode);
        $payload_encoded = str_replace('\n','',$payload_encode);
        
        // dd($payload_encoded);
        

        
        $decodedSignature = base64_decode($header);
        if ($decodedSignature === false) {
            $message ="Failed to decode the base64-encoded signature.";
            return response()->json($message);

        }
        // return response()->json($decodedSignature);
        // Verify the signature
        $verified = openssl_verify(
            $payload_encoded,            // The original payload
            $decodedSignature,   // The provided signature
            $keyResource,          // The public key
            OPENSSL_ALGO_SHA256  // Signature algorithm
        );

        if ($verified === 1) {
            $message = "Signature is valid. Data integrity and authenticity confirmed.";
        } elseif ($verified === 0) {
            $message = "Signature is invalid. Possible tampering or incorrect key.";
        } else {
            $message = "An error occurred during verification: " . openssl_error_string();
        }

        return response()->json(['message' => $message]);
    }
}
