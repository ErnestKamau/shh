<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class TotpTwoFactorController extends Controller
{
    private const VERIFY_MAX_ATTEMPTS = 5;
    private const VERIFY_DECAY_SECONDS = 300;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Verification screen used after password login when TOTP is enabled.
     */
    public function showVerify(): \Illuminate\View\View
    {
        return view('auth.totp');
    }

    public function storeVerify(Request $request, TotpService $totp): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'code' => 'required_without:recovery_code|string',
            'recovery_code' => 'nullable|string',
        ]);

        /** @var \App\User $user */
        $user = Auth::user();

        if (! $user->two_factor_confirmed_at || ! $user->two_factor_secret) {
            session()->forget('totp_required');
            return redirect()->route('home');
        }

        $rateKey = 'totp:' . (string) $user->id;

        if (RateLimiter::tooManyAttempts($rateKey, self::VERIFY_MAX_ATTEMPTS)) {
            $retryAfter = RateLimiter::availableIn($rateKey);
            return redirect()->back()->with('error', 'Too many attempts. Please wait ' . $this->formatWait($retryAfter) . ' and try again.');
        }

        if (trim((string) $request->recovery_code) !== '') {
            $result = $totp->consumeRecoveryCode($user->two_factor_recovery_codes, (string) $request->recovery_code);
            if ($result['valid'] === true) {
                $user->two_factor_recovery_codes = $result['remaining_hashes_json'];
                $user->save();
                session()->forget('totp_required');
                RateLimiter::clear($rateKey);
                return redirect()->route('home');
            }
        }

        $secret = $totp->decryptSecret($user->two_factor_secret);
        if (! $secret) {
            return redirect()->back()->with('error', 'Two-factor configuration is invalid. Please contact support.');
        }

        $acceptedStep = $totp->verifyTotpNewer($secret, (string) $request->code, $user->two_factor_last_used_step);
        if ($acceptedStep === null) {
            RateLimiter::hit($rateKey, self::VERIFY_DECAY_SECONDS);
            $remainingAttempts = max(0, self::VERIFY_MAX_ATTEMPTS - RateLimiter::attempts($rateKey));
            return redirect()->back()->with('error', 'Invalid authenticator code. Remaining attempts: ' . $remainingAttempts . '.');
        }

        $user->two_factor_last_used_step = $acceptedStep;
        $user->save();

        session()->forget('totp_required');
        RateLimiter::clear($rateKey);

        return redirect()->route('home');
    }

    /**
     * Returns JSON used by the profile modal to begin setup.
     */
    public function setup(TotpService $totp): \Illuminate\Http\JsonResponse
    {
        /** @var \App\User $user */
        $user = Auth::user();

        $secret = $totp->generateSecret();
        $user->two_factor_secret = $totp->encryptSecret($secret);
        $user->two_factor_confirmed_at = null;
        $user->two_factor_last_used_step = null;

        // Generate new recovery codes during setup (shown after confirmation)
        $recoveryCodes = $totp->generateRecoveryCodes();
        $user->two_factor_recovery_codes = $totp->hashRecoveryCodes($recoveryCodes);
        $user->save();

        $appName = config('app.name', 'LIMS');
        $uri = $totp->provisioningUri($appName, (string) $user->email, $secret);

        return response()->json([
            'provisioning_uri' => $uri,
            'qr_svg_base64' => $totp->qrSvgBase64($uri),
            'manual_key' => $secret,
        ]);
    }

    public function confirm(Request $request, TotpService $totp): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        /** @var \App\User $user */
        $user = Auth::user();

        $secret = $totp->decryptSecret($user->two_factor_secret);
        if (! $secret) {
            return response()->json(['message' => 'No pending setup found.'], 422);
        }

        if (! $totp->verifyTotp($secret, (string) $request->code)) {
            return response()->json(['message' => 'Invalid code.'], 422);
        }

        $codes = $totp->generateRecoveryCodes();
        $user->two_factor_recovery_codes = $totp->hashRecoveryCodes($codes);
        $user->two_factor_confirmed_at = now();
        $user->two_factor_last_used_step = null;
        $user->save();

        return response()->json([
            'status' => 'enabled',
            'message' => 'Two-factor authentication enabled.',
            'recovery_codes' => $codes,
        ]);
    }

    public function disable(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        /** @var \App\User $user */
        $user = Auth::user();

        if (! Hash::check((string) $request->password, (string) $user->password)) {
            return response()->json(['message' => 'Invalid password.'], 422);
        }

        $user->two_factor_secret = null;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_last_used_step = null;
        $user->save();

        return response()->json([
            'status' => 'disabled',
            'message' => 'Two-factor authentication disabled.',
        ]);
    }

    public function regenerateRecoveryCodes(Request $request, TotpService $totp): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        /** @var \App\User $user */
        $user = Auth::user();

        if (! Hash::check((string) $request->password, (string) $user->password)) {
            return response()->json(['message' => 'Invalid password.'], 422);
        }

        if (! $user->two_factor_confirmed_at) {
            return response()->json(['message' => 'Two-factor is not enabled.'], 422);
        }

        $codes = $totp->generateRecoveryCodes();
        $user->two_factor_recovery_codes = $totp->hashRecoveryCodes($codes);
        $user->save();

        return response()->json([
            'recovery_codes' => $codes,
        ]);
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
