<?php

namespace App\Http\Controllers\MethodSequences;

use App\Http\Controllers\Controller;
use App\Models\MethodSequences\MethodSequence;
use App\Models\MethodSequences\MethodSequenceVersion;
use App\Models\MethodSequences\MethodSequenceStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MethodSequenceController extends Controller
{
    /**
     * Display the method sequences management page.
     */
    public function manage()
    {
        return view('method-sequences.manage');
    }

    /**
     * Display the method sequence stages editor.
     */
    public function stages(MethodSequenceVersion $methodSequenceVersion)
    {
        $methodSequenceVersion->load(['methodSequence.analyte', 'methodSequence.method', 'stages']);
        
        return view('method-sequences.stages', compact('methodSequenceVersion'));
    }

    /**
     * Clone a method sequence with all its versions and stages.
     */
    public function clone(MethodSequence $methodSequence)
    {
        try {
            DB::beginTransaction();

            // Clone the method sequence
            $newSequence = $methodSequence->replicate();
            $newSequence->name = $methodSequence->name . ' (Copy)';
            $newSequence->is_active = false;
            $newSequence->save();

            // Clone all versions
            foreach ($methodSequence->versions as $version) {
                $newVersion = $version->replicate();
                $newVersion->method_sequence_id = $newSequence->id;
                $newVersion->created_by = Auth::id();
                $newVersion->approved_by = null;
                $newVersion->approved_at = null;
                $newVersion->is_active = false;
                $newVersion->save();

                // Clone all stages for this version
                foreach ($version->stages as $stage) {
                    $newStage = $stage->replicate();
                    $newStage->method_sequence_version_id = $newVersion->id;
                    $newStage->save();
                }
            }

            DB::commit();

            return redirect()->route('method-sequences.manage')
                ->with('success', 'Method sequence cloned successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('method-sequences.manage')
                ->with('error', 'Error cloning method sequence: ' . $e->getMessage());
        }
    }
}

