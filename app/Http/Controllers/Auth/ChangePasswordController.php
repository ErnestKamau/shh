<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChangePasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showForm(): View
    {
        return view('auth.change-password');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'new_password'              => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required|string',
        ]);

        /** @var User $user */
        $user = auth()->user();

        $user->password              = bcrypt($request->new_password);
        $user->password_changed_at   = now();
        $user->save();

        return redirect()->intended(route('home'))
            ->with('success', 'Password changed successfully. You are now signed in.');
    }
}
