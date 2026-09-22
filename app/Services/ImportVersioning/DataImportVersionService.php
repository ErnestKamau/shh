<?php

namespace App\Services\ImportVersioning;

use App\Models\DataImportVersion;
use App\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataImportVersionService
{
    /**
     * @param  array<string, mixed>  $changeSummary
     * @param  array{
     *     company_id?: string|null,
     *     bulk_import_batch_id?: string|null,
     *     sample_header_id?: string|null,
     *     lab_section_worksheet_id?: string|null,
     *     notes?: string|null,
     * }  $meta
     */
    public function recordAppliedVersion(
        string $scope,
        string $scopeKey,
        string $filePath,
        ?string $preApplySnapshotPath = null,
        array $changeSummary = [],
        array $meta = [],
        ?User $user = null,
    ): DataImportVersion {
        $user ??= Auth::user();

        return DB::transaction(function () use (
            $scope,
            $scopeKey,
            $filePath,
            $preApplySnapshotPath,
            $changeSummary,
            $meta,
            $user,
        ): DataImportVersion {
            DataImportVersion::query()
                ->where('scope', $scope)
                ->where('scope_key', $scopeKey)
                ->where('status', DataImportVersion::STATUS_APPLIED)
                ->update(['status' => DataImportVersion::STATUS_SUPERSEDED]);

            $nextVersion = (int) DataImportVersion::query()
                ->where('scope', $scope)
                ->where('scope_key', $scopeKey)
                ->max('version') + 1;

            return DataImportVersion::query()->create([
                'company_id' => $meta['company_id'] ?? (function_exists('getUserCompany') ? getUserCompany() : $user?->company_id),
                'user_id' => $user?->id,
                'scope' => $scope,
                'scope_key' => $scopeKey,
                'version' => $nextVersion,
                'status' => DataImportVersion::STATUS_APPLIED,
                'file_path' => $filePath,
                'pre_apply_snapshot_path' => $preApplySnapshotPath,
                'change_summary' => $changeSummary,
                'bulk_import_batch_id' => $meta['bulk_import_batch_id'] ?? null,
                'sample_header_id' => $meta['sample_header_id'] ?? null,
                'lab_section_worksheet_id' => $meta['lab_section_worksheet_id'] ?? null,
                'notes' => $meta['notes'] ?? null,
                'applied_at' => now(),
            ]);
        });
    }

    /**
     * @return Collection<int, DataImportVersion>
     */
    public function listVersions(string $scope, string $scopeKey): Collection
    {
        return DataImportVersion::query()
            ->with('user')
            ->where('scope', $scope)
            ->where('scope_key', $scopeKey)
            ->orderByDesc('version')
            ->get();
    }

    public function findVersion(string $versionId): DataImportVersion
    {
        return DataImportVersion::query()->findOrFail($versionId);
    }

    public function storeUploadedFile(
        string $scope,
        string $scopeKey,
        UploadedFile|string $file,
        int $versionHint = 0,
        string $basename = 'upload.xlsx',
    ): string {
        $safeKey = $this->safePathSegment($scopeKey);
        $versionFolder = max(1, $versionHint);
        $directory = "import-versions/{$scope}/{$safeKey}/v{$versionFolder}";
        Storage::disk('public')->makeDirectory($directory);

        if ($file instanceof UploadedFile) {
            $extension = $file->getClientOriginalExtension() ?: 'xlsx';
            $filename = pathinfo($basename, PATHINFO_FILENAME).'.'.$extension;

            return $file->storeAs($directory, $filename, 'public');
        }

        $contents = (string) $file;
        $path = $directory.'/'.$basename;
        Storage::disk('public')->put($path, $contents);

        return $path;
    }

    public function storeBinarySnapshot(
        string $scope,
        string $scopeKey,
        string $binaryContents,
        int $versionHint = 0,
        string $basename = 'pre-apply-snapshot.xlsx',
    ): string {
        return $this->storeUploadedFile($scope, $scopeKey, $binaryContents, $versionHint, $basename);
    }

    public function nextVersionNumber(string $scope, string $scopeKey): int
    {
        return (int) DataImportVersion::query()
            ->where('scope', $scope)
            ->where('scope_key', $scopeKey)
            ->max('version') + 1;
    }

    public function downloadVersionFile(DataImportVersion $version, string $preferredName = 'import-version.xlsx'): StreamedResponse
    {
        abort_unless($version->fileExists(), 404, 'Version file not found.');

        return Storage::disk('public')->download($version->file_path, $preferredName);
    }

    /**
     * Mark the target as rolled_back intent is recorded by creating a NEW applied
     * version after the caller re-applies the file. This only flips status when
     * restoring an older applied/superseded row's content.
     */
    public function markSupersededAsRolledBack(DataImportVersion $version): void
    {
        if ($version->status === DataImportVersion::STATUS_APPLIED) {
            return;
        }

        $version->status = DataImportVersion::STATUS_ROLLED_BACK;
        $version->save();
    }

    public function absolutePath(string $relativePath): string
    {
        return Storage::disk('public')->path($relativePath);
    }

    protected function safePathSegment(string $value): string
    {
        $safe = preg_replace('/[^A-Za-z0-9_-]+/', '-', $value) ?? 'scope';

        return trim($safe, '-') !== '' ? trim($safe, '-') : 'scope';
    }
}
