<?php

namespace App\Services\DMS;

use App\Models\DMS\DocumentType;
use App\Models\DMS\Document;
use Carbon\Carbon;

class DocumentNumberGenerator
{
    /**
     * Generate document number based on template
     *
     * @param DocumentType $documentType
     * @return string
     */
    public function generate(DocumentType $documentType): string
    {
        $format = $documentType->getInheritedNumberingFormat();
        
        $tokens = [
            '{TYPE_CODE}' => $documentType->code,
            '{YEAR}' => Carbon::now()->format('Y'),
            '{MONTH}' => Carbon::now()->format('m'),
            '{DAY}' => Carbon::now()->format('d'),
            '{PARENT_CODE}' => $documentType->parent ? $documentType->parent->code : '',
            '{SEQ}' => $this->getNextSequence($documentType),
        ];

        $documentNumber = str_replace(array_keys($tokens), array_values($tokens), $format);

        // Ensure uniqueness
        $suffix = 1;
        $originalNumber = $documentNumber;
        
        while (Document::where('document_number', $documentNumber)->exists()) {
            $documentNumber = $originalNumber . '-' . $suffix;
            $suffix++;
        }

        return $documentNumber;
    }

    /**
     * Get the next sequence number for a document type
     *
     * @param DocumentType $documentType
     * @return string
     */
    protected function getNextSequence(DocumentType $documentType): string
    {
        $year = Carbon::now()->format('Y');
        
        $lastDocument = Document::where('document_type_id', $documentType->id)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastDocument) {
            return str_pad('1', 4, '0', STR_PAD_LEFT);
        }

        // Extract sequence number from last document
        // This is a simplified approach - you may need more sophisticated parsing
        preg_match('/(\d+)(?!.*\d)/', $lastDocument->document_number, $matches);
        
        $lastSequence = isset($matches[0]) ? intval($matches[0]) : 0;
        $nextSequence = $lastSequence + 1;

        return str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Parse document number format and extract components
     *
     * @param string $format
     * @return array
     */
    public function parseFormat(string $format): array
    {
        preg_match_all('/\{([^}]+)\}/', $format, $matches);
        
        return $matches[1] ?? [];
    }

    /**
     * Validate document number format
     *
     * @param string $format
     * @return bool
     */
    public function validateFormat(string $format): bool
    {
        $validTokens = [
            'TYPE_CODE',
            'YEAR',
            'MONTH',
            'DAY',
            'SEQ',
            'PARENT_CODE',
        ];

        $tokens = $this->parseFormat($format);

        foreach ($tokens as $token) {
            if (!in_array($token, $validTokens)) {
                return false;
            }
        }

        return true;
    }
}

