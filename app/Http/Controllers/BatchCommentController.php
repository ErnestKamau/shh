<?php

namespace App\Http\Controllers;

use App\BatchComment;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BatchCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|string|exists:users,id',
            'batch_id' => 'required|string|exists:sample_headers,id',
            'type' => 'required|string|max:255',
            'message' => 'required|string',
            'followers' => 'nullable|array',
            'followers.*' => 'string|exists:users,id',
        ]);

        $comment = new BatchComment();
        $comment->created_by = (string) $request->user()->id;
        $comment->reminder_for = $validated['user_id'];
        $comment->personnel_to_cc = ! empty($validated['followers'])
            ? implode(',', $validated['followers'])
            : '';
        $comment->comments = $validated['message'];
        $comment->comment_type = $validated['type'];
        $comment->sample_header_id = $validated['batch_id'];

        $comment->save();

        $companyDetails = getCompanyDetails();
        $batch = getSampleHeaderByID($validated['batch_id']);

        $recipientIds = array_values(array_unique(array_filter(array_merge(
            [$comment->reminder_for],
            $comment->personnel_to_cc !== '' ? explode(',', $comment->personnel_to_cc) : []
        ))));

        foreach ($recipientIds as $recipientId) {
            $user = User::query()->find($recipientId);
            if ($user === null || empty($user->email)) {
                continue;
            }

            $message = 'There is a new note for batch '.$batch->batch_code.'.';
            if ($comment->personnel_to_cc === '') {
                $message .= ' Kindly review the notes.';
            }

            $body = 'Hi '.$user->name.',<br><br>'
                .$message.'<br>
                Regards, <br><br>'
                .$request->user()->name.' ';
            $subject = '['.$companyDetails['name'].'] Batch Notes Notification';
            notify_user($body, $user->email, $subject);
        }

        return redirect()->back()->with('success', 'Batch Comment Added.');
    }

    public function edit(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|string|exists:users,id',
            'batch_id' => 'required|string|exists:sample_headers,id',
            'type' => 'required|string|max:255',
            'message' => 'required|string',
            'followers' => 'nullable|array',
            'followers.*' => 'string|exists:users,id',
        ]);

        $comment = BatchComment::findOrFail($id);
        $comment->created_by = (string) $request->user()->id;
        $comment->reminder_for = $validated['user_id'];
        $comment->personnel_to_cc = ! empty($validated['followers'])
            ? implode(',', $validated['followers'])
            : '';
        $comment->comments = $validated['message'];
        $comment->comment_type = $validated['type'];
        $comment->sample_header_id = $validated['batch_id'];

        if ($request->has('complete')) {
            $comment->completed_at = date('Y-m-d H:i:s');
        }

        $comment->save();

        return redirect()->back()->with('success', 'Batch Comment Edited.');
    }
}
