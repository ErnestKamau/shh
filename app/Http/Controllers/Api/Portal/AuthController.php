<?php

namespace App\Http\Controllers\Api\Portal;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MailController;
use App\Http\Resources\Portal\ContactProfileResource;
use App\Models\CRM\CustomerContact;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // -------------------------------------------------------------------------
    // Step 1 — Validate credentials and trigger 2FA
    // -------------------------------------------------------------------------
    // POST /api/portal/auth/login
    // Body: { "email": "...", "password": "..." }
    //
    // Returns: { "status": "2fa_required", "message": "..." }
    // Does NOT return a token — the client must complete Step 2.
    // -------------------------------------------------------------------------
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
            ->where('active', 1)
            ->first();

        // Use a generic message to avoid revealing whether the email exists
        $invalidMsg = 'These credentials do not match our records.';

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => $invalidMsg], 401);
        }

        // Only CRM contacts (is_client = 1) may use the customer portal
        if ((int) $user->is_client !== 1) {
            return response()->json(['message' => $invalidMsg], 401);
        }

        // Ensure the linked CRM contact still has portal access
        if ($user->crm_contact_id) {
            $contact = CustomerContact::find($user->crm_contact_id);
            if ($contact && !(int) $contact->can_login) {
                return response()->json([
                    'message' => 'Your portal access has been disabled. Please contact support.',
                ], 403);
            }
        }

        // Generate 6-digit code and save it with a 30-minute expiry
        $user->generateTwoFactorCode();

        // Send code via email (reuse existing mail infrastructure)
        $appName = config('app.name', 'LIMS');
        $body = 'Hi ' . ($user->first_name ?? $user->name) . ',<br><br>'
            . 'Your customer portal verification code is:<br><br>'
            . '<strong>' . $user->verify_code . '</strong><br><br>'
            . 'This code expires in 30 minutes.';

        $mailer = new MailController();
        $mailer->html_email([
            'contacts' => [$user->email],
            'subject'  => '[' . $appName . '] Portal Verification Code',
            'body'     => $body,
        ], 'default');

        // Also send SMS when a phone number is configured
        if (trim((string) $user->phone) !== '') {
            sendTextMessage($user->phone, 'Your ' . $appName . ' portal code is ' . $user->verify_code);
        }

        return response()->json([
            'status'  => '2fa_required',
            'message' => 'A verification code has been sent to your email address.',
        ]);
    }

    // -------------------------------------------------------------------------
    // Step 2 — Verify 2FA code and return Bearer token
    // -------------------------------------------------------------------------
    // POST /api/portal/auth/verify-2fa
    // Body: { "email": "...", "code": "123456" }
    //
    // Returns: { "token": "...", "user": { ContactProfileResource } }
    // -------------------------------------------------------------------------
    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string',
        ]);

        $user = User::where('email', $request->email)
            ->where('active', 1)
            ->where('is_client', 1)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid request.'], 422);
        }

        // Check expiry first
        if (!$user->verify_code_expires || now()->isAfter($user->verify_code_expires)) {
            return response()->json([
                'message' => 'The verification code has expired. Please request a new one.',
            ], 422);
        }

        // Check the code matches
        if ((string) $user->verify_code !== (string) $request->code) {
            return response()->json(['message' => 'Invalid verification code.'], 422);
        }

        // Clear the used code
        $user->resetTwoFactor();

        // Revoke any previous portal tokens so only one session is active
        $user->tokens()->where('name', 'portal')->delete();

        // Issue a new Sanctum token
        $token = $user->createToken('portal')->plainTextToken;

        // Load the linked CRM contact profile
        $contact = $user->crm_contact_id
            ? CustomerContact::with('customer')->find($user->crm_contact_id)
            : null;

        return response()->json([
            'token' => $token,
            'user'  => new ContactProfileResource($user, $contact),
        ]);
    }

    // -------------------------------------------------------------------------
    // Resend 2FA code (rate-limited via route throttle middleware)
    // -------------------------------------------------------------------------
    // POST /api/portal/auth/resend-2fa
    // Body: { "email": "..." }
    // -------------------------------------------------------------------------
    public function resendTwoFactor(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)
            ->where('active', 1)
            ->where('is_client', 1)
            ->first();

        // Respond the same way regardless of whether the user exists to
        // prevent email enumeration
        if (!$user) {
            return response()->json([
                'status'  => '2fa_required',
                'message' => 'If that email is registered, a new code will be sent.',
            ]);
        }

        $user->generateTwoFactorCode();

        $appName = config('app.name', 'LIMS');
        $body = 'Hi ' . ($user->first_name ?? $user->name) . ',<br><br>'
            . 'Your new portal verification code is:<br><br>'
            . '<strong>' . $user->verify_code . '</strong><br><br>'
            . 'This code expires in 30 minutes.';

        $mailer = new MailController();
        $mailer->html_email([
            'contacts' => [$user->email],
            'subject'  => '[' . $appName . '] New Portal Verification Code',
            'body'     => $body,
        ], 'default');

        if (trim((string) $user->phone) !== '') {
            sendTextMessage($user->phone, 'Your new ' . $appName . ' portal code is ' . $user->verify_code);
        }

        return response()->json([
            'status'  => '2fa_required',
            'message' => 'If that email is registered, a new code will be sent.',
        ]);
    }

    // -------------------------------------------------------------------------
    // Logout — revoke the current token
    // -------------------------------------------------------------------------
    // POST /api/portal/auth/logout
    // Requires: Authorization: Bearer <token>
    // -------------------------------------------------------------------------
    public function logout(Request $request)
    {
        // Revoke only the current token — tokens() is an Eloquent relation
        $request->user()
            ->tokens()
            ->where('id', $request->user()->currentAccessToken()->id)
            ->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    // -------------------------------------------------------------------------
    // Me — return the authenticated user's profile
    // -------------------------------------------------------------------------
    // GET /api/portal/auth/me
    // Requires: Authorization: Bearer <token>
    // -------------------------------------------------------------------------
    public function me(Request $request)
    {
        $user    = $request->user();
        $contact = $user->crm_contact_id
            ? CustomerContact::with('customer')->find($user->crm_contact_id)
            : null;

        return response()->json(new ContactProfileResource($user, $contact));
    }
}
