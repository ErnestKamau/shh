<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Services\System\SystemBackupCatalog;
use App\Services\System\SystemModuleBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SystemBackupController extends Controller
{
    public function index(Request $request, SystemBackupCatalog $catalog): View
    {
        return view('layouts.configuration.backups', [
            'backupTargets' => $catalog->all(),
            'canExport' => $this->canExport($request->user()),
        ]);
    }

    public function export(Request $request, SystemModuleBackupService $backupService): BinaryFileResponse|RedirectResponse
    {
        if (!$this->canExport($request->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'format' => ['required', 'string', 'in:csv,sql,dump'],
            'targets' => ['required', 'array', 'min:1'],
            'targets.*' => ['required', 'string'],
        ]);

        try {
            $export = $backupService->createExport($validated['targets'], $validated['format']);

            return response()
                ->download($export['path'], $export['filename'], ['Content-Type' => $export['mime']])
                ->deleteFileAfterSend(true);
        } catch (\Throwable $throwable) {
            report($throwable);

            return redirect()
                ->route('system-settings.backups')
                ->withInput()
                ->with('error', 'Unable to generate the requested backup. Please try again.');
        }
    }

    private function canExport(mixed $user): bool
    {
        if (!$user) {
            return false;
        }

        return (method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) || $user->can('system.dashboard.export');
    }
}