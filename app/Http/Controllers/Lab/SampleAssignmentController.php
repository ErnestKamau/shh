<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Services\Sampleworkflow\SampleHeaderAssignmentService;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SampleAssignmentController extends Controller
{
    public function __construct(private readonly SampleHeaderAssignmentService $assignmentService)
    {
        $this->middleware('auth');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'assignee_user_id' => ['required', 'string'],
            'batch_ids' => ['required', 'array', 'min:1'],
            'batch_ids.*' => ['required', 'string'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ], [
            'batch_ids.required' => 'Select at least one batch to assign.',
            'batch_ids.min' => 'Select at least one batch to assign.',
        ]);

        $batchIds = collect($validated['batch_ids'] ?? [])
            ->map(fn ($id): string => trim((string) $id))
            ->filter(fn (string $id): bool => $id !== '' && Str::isUuid($id))
            ->values()
            ->all();

        if ($batchIds === []) {
            return back()->with('error', 'No valid batches were selected for assignment.');
        }

        $actor = $request->user();
        if (! $actor instanceof User) {
            return back()->with('error', 'You must be signed in to assign batches.');
        }

        $result = $this->assignmentService->assignToUser(
            $batchIds,
            (string) $validated['assignee_user_id'],
            $validated['comment'] ?? null,
            $actor,
        );

        if ($result['processed'] === 0) {
            return back()->with('error', 'No batches were assigned. They may be invalid or ineligible.');
        }

        $message = $result['processed'] === 1
            ? '1 batch assigned successfully.'
            : $result['processed'].' batches assigned successfully.';

        if ($result['skipped'] > 0) {
            $message .= ' ('.$result['skipped'].' skipped.)';
        }

        return back()->with('success', $message);
    }
}
