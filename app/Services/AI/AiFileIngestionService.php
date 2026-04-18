<?php

namespace App\Services\AI;

use App\Models\AI\AiChatAttachment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AiFileIngestionService
{
    protected CollectionIndexerService $indexer;
    protected string $apiBaseUrl;

    public function __construct(CollectionIndexerService $indexer)
    {
        $this->indexer    = $indexer;
        $this->apiBaseUrl = AiEndpointResolver::resolve();
    }

    /**
     * Extract text from an uploaded attachment and index it into an
     * ephemeral session collection scoped to the conversation.
     *
     * @param  AiChatAttachment  $attachment
     * @return string  Extracted plain text (may be empty on failure)
     */
    public function ingest(AiChatAttachment $attachment): string
    {
        $attachment->update(['processing_status' => 'pending']);

        try {
            $text = $this->extractText($attachment);

            if (empty(trim($text))) {
                $attachment->update(['processing_status' => 'failed']);
                return '';
            }

            $attachment->update([
                'extracted_text'     => $text,
                'processing_status'  => 'extracted',
            ]);

            // Index into ephemeral session collection so the RAG pipeline can use it
            $collection = 'chat_session_' . $attachment->ai_conversation_id;
            $this->indexer->indexContent(
                $collection,
                $text,
                AiChatAttachment::class,
                $attachment->id,
                ['original_name' => $attachment->original_name, 'session' => true],
                'General.View'
            );

            $attachment->update(['processing_status' => 'indexed']);

            return $text;
        } catch (\Throwable $e) {
            Log::error('AiFileIngestionService: ingest failed', [
                'attachment_id' => $attachment->id,
                'error'         => $e->getMessage(),
            ]);
            $attachment->update(['processing_status' => 'failed']);
            return '';
        }
    }

    /**
     * Extract plain text from the stored file.
     * Supports PDF (via pdfparser) and images (via Google Vision or fallback OCR endpoint).
     */
    protected function extractText(AiChatAttachment $attachment): string
    {
        $path     = Storage::path($attachment->stored_path);
        $mimeType = $attachment->mime_type;

        // PDF extraction
        if ($mimeType === 'application/pdf') {
            return $this->extractPdf($path);
        }

        // Image extraction (PNG / JPEG)
        if (in_array($mimeType, ['image/png', 'image/jpeg', 'image/jpg'])) {
            return $this->extractImage($path);
        }

        return '';
    }

    protected function extractPdf(string $path): string
    {
        // Use smalot/pdfparser if available; fall back to pdftotext if installed.
        if (class_exists(\Smalot\PdfParser\Parser::class)) {
            $parser  = new \Smalot\PdfParser\Parser();
            $pdf     = $parser->parseFile($path);
            return $pdf->getText();
        }

        if (function_exists('shell_exec') && shell_exec('which pdftotext 2>/dev/null')) {
            $escaped = escapeshellarg($path);
            return (string) shell_exec("pdftotext {$escaped} -");
        }

        Log::warning('AiFileIngestionService: no PDF extraction library available (install smalot/pdfparser)');
        return '';
    }

    protected function extractImage(string $path): string
    {
        // Attempt extraction via the AI microservice OCR endpoint
        try {
            $base64  = base64_encode((string) file_get_contents($path));
            $response = Http::timeout(30)->post("{$this->apiBaseUrl}/ai/ocr", [
                'image_base64' => $base64,
            ]);
            if ($response->successful()) {
                return (string) ($response->json('text') ?? '');
            }
        } catch (\Throwable $e) {
            Log::warning('AiFileIngestionService: OCR microservice unavailable', ['error' => $e->getMessage()]);
        }

        return '';
    }
}
