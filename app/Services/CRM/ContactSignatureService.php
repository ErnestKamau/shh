<?php

namespace App\Services\CRM;

use App\Models\CRM\CustomerContact;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContactSignatureService
{
    private const DISK = 'public';

    private const DIRECTORY = 'signatures';

    private const IMAGE_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif', 'webp'];

    private const ALLOWED_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'pdf'];

    /**
     * Drawn pad data takes priority over an uploaded file.
     * Signature remains nullable — returns false when neither source is provided.
     */
    public function applyToContact(CustomerContact $contact, ?UploadedFile $upload = null, string $dataUrl = ''): bool
    {
        $relativePath = $this->resolveRelativePath($upload, $dataUrl);

        if ($relativePath === null) {
            return false;
        }

        $this->deleteStoredSignature($contact->signature);
        $contact->signature = $relativePath;

        return true;
    }

    public function resolveRelativePath(?UploadedFile $upload = null, string $dataUrl = ''): ?string
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
        $extension = strtolower((string) $upload->getClientOriginalExtension());
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            $extension = 'png';
        }

        $filename = 'contact_' . Str::uuid()->toString() . '_' . time() . '.' . $extension;
        $storedPath = $upload->storeAs(self::DIRECTORY, $filename, self::DISK);

        return $storedPath;
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

        if (! in_array($extension, self::IMAGE_EXTENSIONS, true)) {
            return null;
        }

        $imageData = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $decoded = base64_decode($imageData, true);

        if ($decoded === false) {
            return null;
        }

        $filename = 'contact_' . Str::uuid()->toString() . '_' . time() . '.' . $extension;
        $storagePath = self::DIRECTORY . '/' . $filename;
        Storage::disk(self::DISK)->put($storagePath, $decoded);

        return $storagePath;
    }

    public function clearSignature(CustomerContact $contact): void
    {
        $this->deleteStoredSignature($contact->signature);
        $contact->signature = null;
    }

    public function publicUrl(?string $signature): ?string
    {
        $relative = $this->normalizeRelativePath($signature);
        if ($relative === null) {
            return null;
        }

        if (str_starts_with($relative, 'http://') || str_starts_with($relative, 'https://')) {
            return $relative;
        }

        return asset('storage/' . ltrim($relative, '/'));
    }

    public function toDataUri(?string $signature): string
    {
        if ($signature === null || trim($signature) === '') {
            return '';
        }

        $signature = trim($signature);

        if (str_starts_with($signature, 'data:')) {
            return $signature;
        }

        $relative = $this->normalizeRelativePath($signature);
        if ($relative === null) {
            return signatureToDataUri($signature);
        }

        $extension = strtolower(pathinfo($relative, PATHINFO_EXTENSION));
        if ($extension === 'pdf') {
            return '';
        }

        if (! Storage::disk(self::DISK)->exists($relative)) {
            return signatureToDataUri($signature);
        }

        $contents = Storage::disk(self::DISK)->get($relative);
        if ($contents === null || $contents === '') {
            return '';
        }

        $mime = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }

    public function hasUsableImageSignature(?string $signature): bool
    {
        return $this->toDataUri($signature) !== '';
    }

    public function isPdfSignature(?string $signature): bool
    {
        $relative = $this->normalizeRelativePath($signature);
        if ($relative === null) {
            return false;
        }

        return strtolower(pathinfo($relative, PATHINFO_EXTENSION)) === 'pdf';
    }

    public function dataUriForContact(?CustomerContact $contact): string
    {
        if ($contact === null) {
            return '';
        }

        return $this->toDataUri($contact->signature);
    }

    public function publicUrlForContact(?CustomerContact $contact): ?string
    {
        if ($contact === null) {
            return null;
        }

        return $this->publicUrl($contact->signature);
    }

    private function normalizeRelativePath(?string $signature): ?string
    {
        if ($signature === null) {
            return null;
        }

        $signature = trim($signature);
        if ($signature === '' || str_starts_with($signature, 'data:')) {
            return null;
        }

        if (str_starts_with($signature, 'http://') || str_starts_with($signature, 'https://')) {
            return $signature;
        }

        $signature = ltrim($signature, '/');
        if (str_starts_with($signature, 'storage/')) {
            $signature = substr($signature, strlen('storage/'));
        }

        return $signature !== '' ? $signature : null;
    }

    private function deleteStoredSignature(?string $signature): void
    {
        $relative = $this->normalizeRelativePath($signature);
        if ($relative === null || str_starts_with($relative, 'http://') || str_starts_with($relative, 'https://')) {
            return;
        }

        if (Storage::disk(self::DISK)->exists($relative)) {
            Storage::disk(self::DISK)->delete($relative);
        }
    }
}
