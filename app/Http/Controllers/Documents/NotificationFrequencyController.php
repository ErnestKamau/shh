<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\NotificationFrequency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationFrequencyController extends Controller
{
    public function index()
    {
        return view('documents.notification-frequencies.index');
    }

    public function create()
    {
        return view('documents.notification-frequencies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:notification_frequencies',
            'description' => 'nullable|string|max:500',
            'days_interval' => 'required|integer|min:1|max:365',
        ]);

        NotificationFrequency::create([
            'name' => $request->name,
            'description' => $request->description,
            'days_interval' => $request->days_interval,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('notification-frequencies.index')
            ->with('success', 'Notification frequency created successfully.');
    }

    public function show($id)
    {
        $notificationFrequency = NotificationFrequency::withCount('documents')->findOrFail($id);
        
        return view('documents.notification-frequencies.show', compact('notificationFrequency'));
    }

    public function edit($id)
    {
        $notificationFrequency = NotificationFrequency::withCount('documents')->findOrFail($id);
        
        return view('documents.notification-frequencies.edit', compact('notificationFrequency'));
    }

    public function update(Request $request, $id)
    {
        $notificationFrequency = NotificationFrequency::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255|unique:notification_frequencies,name,' . $id,
            'description' => 'nullable|string|max:500',
            'days_interval' => 'required|integer|min:1|max:365',
        ]);

        $notificationFrequency->update([
            'name' => $request->name,
            'description' => $request->description,
            'days_interval' => $request->days_interval,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('notification-frequencies.index')
            ->with('success', 'Notification frequency updated successfully.');
    }

    public function destroy($id)
    {
        $notificationFrequency = NotificationFrequency::findOrFail($id);
        
        // Check if frequency is being used by any documents
        if ($notificationFrequency->documents()->count() > 0) {
            return redirect()->route('notification-frequencies.index')
                ->with('error', 'Cannot delete frequency that is being used by documents.');
        }
        
        $notificationFrequency->delete();

        return redirect()->route('notification-frequencies.index')
            ->with('success', 'Notification frequency deleted successfully.');
    }
}
