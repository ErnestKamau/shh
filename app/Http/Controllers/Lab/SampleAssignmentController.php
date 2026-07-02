<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Services\Sampleworkflow\SampleHeaderAssignmentService;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

    public function users(Request $request): JsonResponse
    {
        $actor = $request->user();
        $actorId = $actor instanceof User ? (string) $actor->id : null;
        $actorCompanyId = $actor instanceof User ? (string) ($actor->company_id ?? '') : '';

        $usersQuery = User::query()
            ->where('active', 1)
            ->where(function ($query): void {
                $query->where('is_client', 0)
                    ->orWhereNull('is_client');
            });

        if ($actorCompanyId !== '') {
            $usersQuery->where('company_id', $actorCompanyId);
        }

        $users = $usersQuery
            ->orderBy('name')
            ->get(['id', 'name', 'first_name', 'middle_name', 'last_name'])
            ->map(fn (User $user): array => [
                'id' => (string) $user->id,
                'name' => $this->resolveDisplayName($user),
            ])
            ->values();

        Log::info('Sample assignment users endpoint loaded.', [
            'actor_id' => $actorId,
            'actor_company_id' => $actorCompanyId !== '' ? $actorCompanyId : null,
            'request_path' => $request->path(),
            'users_count' => $users->count(),
        ]);

        if ($users->isEmpty()) {
            Log::warning('Sample assignment users endpoint returned no users.', [
                'actor_id' => $actorId,
                'actor_company_id' => $actorCompanyId !== '' ? $actorCompanyId : null,
                'active_non_client_total' => User::query()
                    ->where('active', 1)
                    ->where(function ($query): void {
                        $query->where('is_client', 0)
                            ->orWhereNull('is_client');
                    })
                    ->count(),
            ]);
        }

        return response()->json([
            'users' => $users,
            'count' => $users->count(),
            'debug_message' => $users->isEmpty()
                ? 'No personnel matched active/non-client filters for your company context.'
                : null,
        ]);
    }

    private function resolveDisplayName(User $user): string
    {
        $name = trim((string) ($user->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        $parts = [
            trim((string) ($user->first_name ?? '')),
            trim((string) ($user->middle_name ?? '')),
            trim((string) ($user->last_name ?? '')),
        ];

        $fullName = trim(implode(' ', array_values(array_filter($parts, fn (string $part): bool => $part !== ''))));
        if ($fullName !== '') {
            return $fullName;
        }

        return 'Personnel '.substr((string) $user->id, 0, 8);
    }
}
