<?php

namespace App\Http\Controllers;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormPermission;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SubmissionFormController extends Controller
{
    /**
     * Display a listing of submission forms
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = SubmissionForm::with(['creator', 'sections'])
            ->withCount(['sections', 'instances']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'published') {
                $query->where('is_published', true);
            } elseif ($status === 'draft') {
                $query->where('is_published', false);
            } elseif ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by creator
        if ($request->filled('creator')) {
            $query->where('created_by', $request->get('creator'));
        }

        $forms = $query->orderBy('created_at', 'desc')
                      ->paginate(15)
                      ->withQueryString();

        return view('submission-forms.index', compact('forms'));
    }

    /**
     * Show the form for creating a new submission form
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('submission-forms.create');
    }

    /**
     * Store a newly created submission form
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:submission_forms,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'naming_convention_prefix' => ['required', 'string', 'max:50'],
            'naming_convention_format' => ['required', 'string', 'max:100'],
            'is_active' => ['boolean']
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_published'] = false; // New forms start as drafts
        $validated['version'] = '1.0';

        $form = SubmissionForm::create($validated);

        return redirect()
            ->route('submission-forms.show', $form)
            ->with('success', 'Submission form created successfully. You can now add sections and elements.');
    }

    /**
     * Display the specified submission form
     * 
     * @param SubmissionForm $submissionForm
     * @return \Illuminate\View\View
     */
    public function show(SubmissionForm $submissionForm)
    {
        $submissionForm->load([
            'sections.elementHolders.elements',
            'creator'
        ]);

        $statistics = $submissionForm->getStatistics();

        return view('submission-forms.show', compact('submissionForm', 'statistics'));
    }

    /**
     * Show the form for editing the specified submission form
     * 
     * @param SubmissionForm $submissionForm
     * @return \Illuminate\View\View
     */
    public function edit(SubmissionForm $submissionForm)
    {
        return view('submission-forms.edit', compact('submissionForm'));
    }

    /**
     * Update the specified submission form
     * 
     * @param Request $request
     * @param SubmissionForm $submissionForm
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, SubmissionForm $submissionForm)
    {
        $validated = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('submission_forms', 'name')->ignore($submissionForm->id)
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'naming_convention_prefix' => ['required', 'string', 'max:50'],
            'naming_convention_format' => ['required', 'string', 'max:100'],
            'is_active' => ['boolean']
        ]);

        $submissionForm->update($validated);

        return redirect()
            ->route('submission-forms.show', $submissionForm)
            ->with('success', 'Submission form updated successfully.');
    }

    /**
     * Remove the specified submission form
     * 
     * @param SubmissionForm $submissionForm
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(SubmissionForm $submissionForm)
    {
        // Check if form has instances
        if ($submissionForm->instances()->exists()) {
            return redirect()
                ->route('submission-forms.show', $submissionForm)
                ->with('error', 'Cannot delete form that has submission instances. Please archive it instead.');
        }

        $formName = $submissionForm->name;
        $submissionForm->delete();

        return redirect()
            ->route('submission-forms.index')
            ->with('success', "Submission form '{$formName}' deleted successfully.");
    }

    /**
     * Preview the form as users will see it
     * 
     * @param SubmissionForm $submissionForm
     * @return \Illuminate\View\View
     */
    public function preview(SubmissionForm $submissionForm)
    {
        $submissionForm->load([
            'sections.elementHolders.elements' => function($query) {
                $query->orderBy('sort_order');
            }
        ]);

        return view('submission-forms.preview', compact('submissionForm'));
    }

    /**
     * Toggle the published status of the form
     * 
     * @param SubmissionForm $submissionForm
     * @return \Illuminate\Http\RedirectResponse
     */
    public function togglePublished(SubmissionForm $submissionForm)
    {
        // Validate form has sections before publishing
        if (!$submissionForm->is_published && !$submissionForm->hasSections()) {
            return redirect()
                ->route('submission-forms.show', $submissionForm)
                ->with('error', 'Cannot publish form without sections. Please add at least one section first.');
        }

        $submissionForm->update([
            'is_published' => !$submissionForm->is_published
        ]);

        $status = $submissionForm->is_published ? 'published' : 'unpublished';
        
        return redirect()
            ->route('submission-forms.show', $submissionForm)
            ->with('success', "Form has been {$status} successfully.");
    }

    /**
     * Clone an existing form
     * 
     * @param SubmissionForm $submissionForm
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clone(SubmissionForm $submissionForm)
    {
        $clonedForm = $submissionForm->replicate([
            'created_by',
            'is_published',
            'created_at',
            'updated_at'
        ]);

        $clonedForm->name = $submissionForm->name . ' (Copy)';
        $clonedForm->created_by = Auth::id();
        $clonedForm->is_published = false;
        $clonedForm->save();

        // Clone sections, holders, and elements
        foreach ($submissionForm->sections as $section) {
            $clonedSection = $section->replicate(['submission_form_id']);
            $clonedSection->submission_form_id = $clonedForm->id;
            $clonedSection->save();

            foreach ($section->elementHolders as $holder) {
                $clonedHolder = $holder->replicate(['submission_form_section_id']);
                $clonedHolder->submission_form_section_id = $clonedSection->id;
                $clonedHolder->save();

                foreach ($holder->elements as $element) {
                    $clonedElement = $element->replicate(['submission_form_element_holder_id']);
                    $clonedElement->submission_form_element_holder_id = $clonedHolder->id;
                    $clonedElement->save();
                }
            }
        }

        return redirect()
            ->route('submission-forms.show', $clonedForm)
            ->with('success', 'Form cloned successfully. You can now modify the copy.');
    }

    /**
     * Export form structure as JSON
     */
    public function export(SubmissionForm $submissionForm)
    {
        $submissionForm->load([
            'sections.elementHolders.elements'
        ]);

        $exportData = [
            'form' => $submissionForm->only([
                'name', 'description', 'naming_convention_prefix', 
                'naming_convention_format', 'version'
            ]),
            'sections' => $submissionForm->sections->map(function($section) {
                return [
                    'title' => $section->title,
                    'description' => $section->description,
                    'sort_order' => $section->sort_order,
                    'element_holders' => $section->elementHolders->map(function($holder) {
                        return [
                            'holder_type' => $holder->holder_type,
                            'max_elements' => $holder->max_elements,
                            'sort_order' => $holder->sort_order,
                            'elements' => $holder->elements->map(function($element) {
                                return $element->only([
                                    'element_type', 'label', 'name', 'placeholder',
                                    'help_text', 'is_required', 'is_readonly',
                                    'default_value', 'validation_rules', 'options',
                                    'calculation_formula', 'conditional_logic', 'sort_order'
                                ]);
                            })
                        ];
                    })
                ];
            })
        ];

        $filename = str_replace(' ', '_', strtolower($submissionForm->name)) . '_export.json';

        return response()->json($exportData)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }


}