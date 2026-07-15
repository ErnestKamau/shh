<?php

namespace Tests\Unit\Services\Personnel;

use App\Services\Personnel\PersonnelSignatureService;
use App\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PersonnelSignatureServiceTest extends TestCase
{
    public function test_store_from_data_url_returns_public_path(): void
    {
        Storage::fake('public');

        $service = new PersonnelSignatureService();
        $pngBase64 = base64_encode(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));
        $dataUrl = 'data:image/png;base64,' . $pngBase64;

        $path = $service->storeFromDataUrl($dataUrl);

        $this->assertNotNull($path);
        $this->assertStringStartsWith('/storage/personnel-signature/', $path);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $path));
    }

    public function test_store_from_data_url_rejects_invalid_payload(): void
    {
        Storage::fake('public');

        $service = new PersonnelSignatureService();

        $this->assertNull($service->storeFromDataUrl(''));
        $this->assertNull($service->storeFromDataUrl('not-a-data-url'));
        $this->assertNull($service->storeFromDataUrl('data:image/svg+xml;base64,abc'));
    }

    public function test_drawn_signature_takes_priority_over_upload(): void
    {
        Storage::fake('public');

        $service = new PersonnelSignatureService();
        $upload = UploadedFile::fake()->image('upload-sig.png', 40, 20);
        $pngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';
        $dataUrl = 'data:image/png;base64,' . $pngBase64;

        $user = new User();
        $applied = $service->applyToUser($user, $upload, $dataUrl);

        $this->assertTrue($applied);
        $this->assertNotNull($user->electronic_sig);
        $this->assertStringContainsString('personnel-signature/', $user->electronic_sig);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $user->electronic_sig));
    }

    public function test_upload_is_used_when_pad_is_empty(): void
    {
        Storage::fake('public');

        $service = new PersonnelSignatureService();
        $upload = UploadedFile::fake()->image('upload-sig.jpg', 40, 20);
        $user = new User();

        $applied = $service->applyToUser($user, $upload, '');

        $this->assertTrue($applied);
        $this->assertStringEndsWith('.jpg', $user->electronic_sig);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $user->electronic_sig));
    }
}
