<?php

namespace App\Http\Controllers;

use App\Support\UserManualRegistry;
use Illuminate\View\View;

class UserManualController extends Controller
{
    public function index(): View
    {
        return view('layouts.usermanual.hub', [
            'manuals' => UserManualRegistry::manuals(),
            'activeManual' => null,
            'activeChapter' => null,
        ]);
    }

    public function show(string $manual, ?string $chapter = null): View
    {
        $manualData = UserManualRegistry::find($manual);

        abort_if($manualData === null, 404);

        $chapters = $manualData['chapters'];
        $chapterSlug = $chapter ?: ($chapters[0]['slug'] ?? 'overview');
        $chapterExists = collect($chapters)->contains(fn (array $c): bool => $c['slug'] === $chapterSlug);

        abort_if(! $chapterExists, 404);

        return view('layouts.usermanual.show', [
            'manuals' => UserManualRegistry::manuals(),
            'manualSlug' => $manual,
            'manual' => $manualData,
            'activeManual' => $manual,
            'activeChapter' => $chapterSlug,
            'chapterPartial' => "layouts.usermanual.chapters.{$manual}.{$chapterSlug}",
        ]);
    }
}
