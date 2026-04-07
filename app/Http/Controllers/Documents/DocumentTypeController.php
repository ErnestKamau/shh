<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'twofactor']);
    }

    public function index()
    {
        // Since we're using Livewire, we don't need to pass data here
        return view('documents.types.index');
    }

    public function create()
    {
        return view('documents.types.create');
    }

    public function show($id)
    {
        $documentType = DocumentType::withCount('documents')->findOrFail($id);
        return view('documents.types.show', compact('documentType'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:document_types,code',
            'description' => 'nullable|string'
        ]);

        DocumentType::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'description' => $request->description,
            'created_by' => Auth::id()
        ]);

        return redirect()->route('documents.types.index')
            ->with('success', 'Document type created successfully.');
    }

    public function edit($id)
    {
        $documentType = DocumentType::findOrFail($id);
        return view('documents.types.edit', compact('documentType'));
    }

    public function update(Request $request, $id)
    {
        $documentType = DocumentType::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:document_types,code,' . $id,
            'description' => 'nullable|string'
        ]);

        $documentType->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'description' => $request->description,
            'updated_by' => Auth::id()
        ]);

        return redirect()->route('documents.types.index')
            ->with('success', 'Document type updated successfully.');
    }

    public function destroy($id)
    {
        $documentType = DocumentType::findOrFail($id);
        
        // Check if there are documents using this type
        if ($documentType->documents()->count() > 0) {
            return redirect()->route('documents.types.index')
                ->with('error', 'Cannot delete document type. It has associated documents.');
        }

        $documentType->update([
            'is_active' => false,
            'updated_by' => Auth::id()
        ]);

        return redirect()->route('documents.types.index')
            ->with('success', 'Document type deleted successfully.');
    }
}
