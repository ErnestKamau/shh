<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TotpService
{
    public function __construct(private Google2FA $google2fa)
    {
    }

    public function generateSecret(int $length = 32): string
    {
        return $this->google2fa->generateSecretKey($length);
    }

    public function encryptSecret(string $secret): string
    {
        return Crypt::encryptString($secret);
    }

    public function decryptSecret(?string $encryptedSecret): ?string
    {
        if (! is_string($encryptedSecret) || $encryptedSecret === '') {
            return null;
        }

        return Crypt::decryptString($encryptedSecret);
    }

    public function provisioningUri(string $appName, string $email, string $secret): string
    {
        $label = rawurlencode($appName . ':' . $email);
        $issuer = rawurlencode($appName);

        return "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * @return string Base64-encoded SVG data (no data: prefix)
     */
    public function qrSvgBase64(string $provisioningUri, int $size = 220): string
    {
        // Use SVG to avoid requiring Imagick for PNG rendering.
        $svg = QrCode::format('svg')->size($size)->margin(1)->generate($provisioningUri);

        return base64_encode($svg);
    }

    public function verifyTotp(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\s+/', '', $code) ?? '';

        return $this->google2fa->verifyKey($secret, $code, $window) === true;
    }

    /**
     * Verify a code AND return the accepted timestamp step (for replay protection).
     *
     * @return int|null Accepted step, or null if invalid / replayed.
     */
    public function verifyTotpNewer(string $secret, string $code, ?int $oldStep, int $window = 1): ?int
    {
        $code = preg_replace('/\s+/', '', $code) ?? '';

        if ($oldStep === null) {
            $result = $this->google2fa->verifyKey($secret, $code, $window);
            return $result === true ? $this->google2fa->getTimestamp() : null;
        }

        $result = $this->google2fa->verifyKeyNewer($secret, $code, $oldStep, $window);

        return is_int($result) ? $result : null;
    }

    /**
     * @return array<int, string> Plaintext recovery codes (show once)
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $a = strtoupper(Str::random(5));
            $b = strtoupper(Str::random(5));
            $codes[] = $a . '-' . $b;
        }

        return $codes;
    }

    public function hashRecoveryCodes(array $plainCodes): string
    {
        $hashes = array_map(fn (string $c): string => Hash::make($c), $plainCodes);

        return json_encode(array_values($hashes), JSON_THROW_ON_ERROR);
    }

    /**
     * @return array{valid: bool, remaining_hashes_json: string|null}
     */
    public function consumeRecoveryCode(?string $storedHashesJson, string $submittedCode): array
    {
        $submittedCode = strtoupper(trim($submittedCode));

        $hashes = [];

        if (is_string($storedHashesJson) && $storedHashesJson !== '') {
            try {
                $decoded = json_decode($storedHashesJson, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($decoded)) {
                    $hashes = $decoded;
                }
            } catch (\Throwable) {
                $hashes = [];
            }
        }

        $indexToRemove = null;

        foreach ($hashes as $i => $hash) {
            if (is_string($hash) && Hash::check($submittedCode, $hash)) {
                $indexToRemove = $i;
                break;
            }
        }

        if ($indexToRemove === null) {
            return ['valid' => false, 'remaining_hashes_json' => $storedHashesJson];
        }

        unset($hashes[$indexToRemove]);

        return [
            'valid' => true,
            'remaining_hashes_json' => json_encode(array_values($hashes), JSON_THROW_ON_ERROR),
        ];
    }
}

