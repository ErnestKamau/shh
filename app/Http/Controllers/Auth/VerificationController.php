<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;
use Throwable;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails {
        verify as protected traitVerify;
        resend as protected traitResend;
    }

    /**
     * Where to redirect users after verification.
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
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    public function verify(Request $request)
    {
        try {
            return $this->traitVerify($request);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->back()->with('error', 'Email verification failed. Please try again.');
        }
    }

    public function resend(Request $request)
    {
        try {
            return $this->traitResend($request);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->back()->with('error', 'Unable to resend verification email. Please try again.');
        }
    }
}
