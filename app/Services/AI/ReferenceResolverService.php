<?php

namespace App\Services\AI;

use App\Models\DMS\Document;
use Illuminate\Support\Facades\Log;

/**
 * ReferenceResolverService
 * 
 * Handles resolving manual document references (@mentions) into 
 * full text context for the AI inference engine.
 */
class ReferenceResolverService
{
    /**
     * Resolve a list of document IDs into a grounded context string.
     *
     * @param array $ids
     * @return string
     */
    public function resolveReferences(array $ids): string
    {
        if (empty($ids)) {
            return '';
        }

        $documents = Document::whereIn('id', $ids)
            ->where('is_kb_indexed', true)
            ->get(['id', 'title', 'kb_content', 'description']);

        $contextParts = [];

        foreach ($documents as $doc) {
            $content = $doc->kb_content ?: $doc->description;
            if ($content) {
                $contextParts[] = "--- DOCUMENT REFERENCE: {$doc->title} ---\n{$content}";
            }
        }

        if (empty($contextParts)) {
            return '';
        }

        return "\n\nUSE THE FOLLOWING MANUALLY REFERENCED DOCUMENTS AS PRIMARY CONTEXT:\n" . implode("\n\n", $contextParts);
    }
}
