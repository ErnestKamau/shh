<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Services\System\DatabaseExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SystemDatabaseExportController extends Controller
{
    public function __invoke(Request $request, string $format, DatabaseExportService $databaseExportService): BinaryFileResponse|RedirectResponse
    {
        $user = $request->user();

        if (!$user || !((method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) || $user->can('system.dashboard.export'))) {
            abort(403);
        }

        try {
            $connection = $request->query('connection');
            $export = $databaseExportService->createExport(
                $format,
                is_string($connection) ? trim($connection) : null
            );

            return response()
                ->download($export['path'], $export['filename'], ['Content-Type' => $export['mime']])
                ->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('system-settings')
                ->with('error', __('system.database_export_failed'));
        }
    }
}
