<?php

namespace Modules\TemplateEngine\Observers;

use Modules\TemplateEngine\Models\TemplateSubmission;
use Modules\TemplateEngine\Services\FileUploadService;

class TemplateSubmissionObserver
{
    protected $fileUploadService;

    public function __construct()
    {
        $this->fileUploadService = app(FileUploadService::class);
    }

    /**
     * Handle the TemplateSubmission "deleting" event.
     *
     * @param TemplateSubmission $submission
     * @return void
     */
    public function deleting(TemplateSubmission $submission): void
    {
        // Get submission data
        $data = $submission->data ?? [];
        
        if (empty($data)) {
            return;
        }
        
        // Get template fields to identify image_upload fields
        $template = $submission->template()->with('fields')->first();
        
        if (!$template) {
            return;
        }
        
        // Find and delete uploaded files
        foreach ($template->fields as $field) {
            if ($field->type === 'image_upload' && isset($data[$field->name]) && $data[$field->name]) {
                $filePath = $data[$field->name];
                
                // Delete the file
                $this->fileUploadService->deleteFile($filePath);
            }
        }
        
        // Delete the submission-specific directory if it exists
        $submissionDirectory = 'template-images/submissions/' . $submission->id;
        $this->fileUploadService->deleteDirectory($submissionDirectory);
    }

    /**
     * Handle the TemplateSubmission "deleted" event.
     *
     * @param TemplateSubmission $submission
     * @return void
     */
    public function deleted(TemplateSubmission $submission): void
    {
        // Additional cleanup if needed after deletion
    }
}

