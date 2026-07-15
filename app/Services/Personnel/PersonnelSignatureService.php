<?php

namespace App\Services\Personnel;

use App\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PersonnelSignatureService
{
    private const DISK = 'public';

    private const DIRECTORY = 'personnel-signature';

    private const ALLOWED_EXTENSIONS = ['png', 'jpg', 'gif', 'webp'];

    /**
     * Drawn pad data takes priority over an uploaded file.
     */
    public function applyToUser(User $user, ?UploadedFile $upload = null, string $dataUrl = ''): bool
    {
        $publicPath = $this->resolvePublicPath($upload, $dataUrl);

        if ($publicPath === null) {
            return false;
        }

        $user->electronic_sig = $publicPath;

        return true;
    }

    public function resolvePublicPath(?UploadedFile $upload = null, string $dataUrl = ''): ?string
    {
        $fromPad = $this->storeFromDataUrl($dataUrl);
        if ($fromPad !== null) {
            return $fromPad;
        }

        if ($upload !== null) {
            return $this->storeFromUpload($upload);
        }

        return null;
    }

    public function storeFromUpload(UploadedFile $upload): string
    {
        $filename = Str::uuid()->toString() . '_' . time() . '.' . $upload->getClientOriginalExtension();
        $storedPath = $upload->storeAs(self::DIRECTORY, $filename, self::DISK);

        return '/storage/' . $storedPath;
    }

    public function storeFromDataUrl(string $dataUrl): ?string
    {
        if ($dataUrl === '' || ! str_starts_with($dataUrl, 'data:image/')) {
            return null;
        }

        if (! preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $matches)) {
            return null;
        }

        $extension = strtolower($matches[1]);
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            return null;
        }

        $imageData = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $decoded = base64_decode($imageData, true);

        if ($decoded === false) {
            return null;
        }

        $filename = Str::uuid()->toString() . '_' . time() . '.' . $extension;
        $storagePath = self::DIRECTORY . '/' . $filename;
        Storage::disk(self::DISK)->put($storagePath, $decoded);

        return '/storage/' . $storagePath;
    }
}
