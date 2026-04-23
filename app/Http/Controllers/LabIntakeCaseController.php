<?php

namespace App\Http\Controllers;

use App\Models\SubmissionFormInstance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LabIntakeCaseController extends Controller
{
    public function confirm(Request $request, int $instance): RedirectResponse
    {
        return $this->applyDecision($request, $instance, 'confirmed');
    }

    public function accept(Request $request, int $instance): RedirectResponse
    {
        return $this->applyDecision($request, $instance, 'accepted');
    }

    public function reject(Request $request, int $instance): RedirectResponse
    {
        return $this->applyDecision($request, $instance, 'rejected');
    }

    protected function applyDecision(Request $request, int $instance, string $decision): RedirectResponse
    {
        $formInstance = SubmissionFormInstance::query()->findOrFail($instance);

        $notes = trim((string) $request->input('notes', ''));
        $statusNotes = $decision;
        if ($notes !== '') {
            $statusNotes .= ' - ' . $notes;
        }

        $formInstance->update([
            'reviewed_by' => optional($request->user())->id,
            'reviewed_at' => now(),
            'review_notes' => $statusNotes,
        ]);

        return redirect()->back()->with('success', 'Intake case ' . $decision . ' successfully.');
    }
}
