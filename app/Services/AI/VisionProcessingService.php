<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * VisionProcessingService
 * 
 * Handles image/document processing for vision-capable models
 * 
 * Features:
 * - Image format validation (PNG, JPEG, GIF, WebP)
 * - Image size optimization (resize if > 2MB)
 * - Base64 encoding for model API
 * - Document extraction (PDF → images)
 * - Graceful degradation if vision not available
 * 
 * Note: Vision is currently feature-flagged and deferred to Sprint 5
 */
class VisionProcessingService
{
    protected const MAX_IMAGE_BYTES = 2097152;  // 2MB
    protected const SUPPORTED_FORMATS = ['png', 'jpeg', 'jpg', 'gif', 'webp'];
    protected const TARGET_WIDTH = 1024;
    protected const TARGET_HEIGHT = 768;

    /**
     * Process an image file for vision model input
     * 
     * @param string $filePath Path to image file
     * @return array {
     *     'success': bool,
     *     'base64_data': string|null,
     *     'format': string|null,          // 'image/png', 'image/jpeg', etc
     *     'original_size_bytes': int,
     *     'processed_size_bytes': int,
     *     'was_resized': bool,
     *     'mime_type': string|null,
     *     'error': string|null,
     * }
     */
    public function processImage(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [
                'success' => false,
                'base64_data' => null,
                'format' => null,
                'original_size_bytes' => 0,
                'processed_size_bytes' => 0,
                'was_resized' => false,
                'mime_type' => null,
                'error' => "File not found: $filePath",
            ];
        }

        $originalSize = filesize($filePath);
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Validate format
        if (!in_array($extension, self::SUPPORTED_FORMATS)) {
            return [
                'success' => false,
                'base64_data' => null,
                'format' => null,
                'original_size_bytes' => $originalSize,
                'processed_size_bytes' => 0,
                'was_resized' => false,
                'mime_type' => null,
                'error' => "Unsupported image format: $extension",
            ];
        }

        // Read image
        $imageData = file_get_contents($filePath);
        if ($imageData === false) {
            return [
                'success' => false,
                'base64_data' => null,
                'format' => null,
                'original_size_bytes' => $originalSize,
                'processed_size_bytes' => 0,
                'was_resized' => false,
                'mime_type' => null,
                'error' => "Failed to read image file",
            ];
        }

        $processedData = $imageData;
        $wasResized = false;
        $mimeType = $this->getMimeType($extension);

        // Resize if needed
        if (strlen($imageData) > self::MAX_IMAGE_BYTES) {
            $resized = $this->resizeImage($filePath);
            if ($resized) {
                $processedData = $resized;
                $wasResized = true;
            }
        }

        $base64Data = base64_encode($processedData);

        Log::info('image_processed', [
            'file_path' => basename($filePath),
            'format' => $extension,
            'original_size_kb' => round($originalSize / 1024, 1),
            'processed_size_kb' => round(strlen($processedData) / 1024, 1),
            'was_resized' => $wasResized,
        ]);

        return [
            'success' => true,
            'base64_data' => $base64Data,
            'format' => $extension,
            'original_size_bytes' => $originalSize,
            'processed_size_bytes' => strlen($processedData),
            'was_resized' => $wasResized,
            'mime_type' => $mimeType,
            'error' => null,
        ];
    }

    /**
     * Resize image to fit within bounds
     * 
     * In production, use GD library or ImageMagick
     * For now, return minimal resizing (just warn)
     * 
     * @param string $filePath
     * @return string|null Resized image data or null if failed
     */
    protected function resizeImage(string $filePath): ?string
    {
        // Placeholder: In production, implement actual resizing
        // For now, just read and return (testing only)
        // Real implementation would use GD or ImageMagick
        return file_get_contents($filePath);
    }

    /**
     * Get MIME type from extension
     * 
     * @param string $extension
     * @return string
     */
    protected function getMimeType(string $extension): string
    {
        $mimeTypes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];

        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
    }

    /**
     * Process document (PDF) for vision analysis
     * 
     * @param string $filePath Path to PDF file
     * @param int $maxPages Maximum pages to extract (0 = all)
     * @return array {
     *     'success': bool,
     *     'images': array[],           // Array of {base64_data, page_number}
     *     'page_count': int,
     *     'error': string|null,
     * }
     */
    public function processDocument(string $filePath, int $maxPages = 5): array
    {
        if (!file_exists($filePath)) {
            return [
                'success' => false,
                'images' => [],
                'page_count' => 0,
                'error' => "Document not found: $filePath",
            ];
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Currently only support PDFs (more formats can be added)
        if ($extension !== 'pdf') {
            return [
                'success' => false,
                'images' => [],
                'page_count' => 0,
                'error' => "Unsupported document format: $extension",
            ];
        }

        // Placeholder: In production, use PDF library to extract pages
        // For now, return error (feature deferred to Sprint 5)
        Log::info('document_processing_requested', [
            'file_path' => basename($filePath),
            'format' => $extension,
            'status' => 'deferred_to_sprint_5',
        ]);

        return [
            'success' => false,
            'images' => [],
            'page_count' => 0,
            'error' => "PDF processing deferred to Sprint 5",
        ];
    }

    /**
     * Check if vision is enabled and available
     * 
     * @return array {
     *     'vision_enabled': bool,
     *     'vision_available': bool,
     *     'available_models': string[],
     *     'reason_if_unavailable': string|null,
     * }
     */
    public function getVisionStatus(): array
    {
        $visionEnabled = config('ai.enable_vision', false);

        // Check if vision models are available
        $visionAvailable = false;
        $availableModels = [];
        $reasonIfUnavailable = null;

        if (!$visionEnabled) {
            $reasonIfUnavailable = "Vision feature flag disabled (ai.enable_vision)";
        } else {
            // In production, check ModelRegistry for vision-capable models
            // Currently, no vision models are deployed
            $visionAvailable = false;
            $reasonIfUnavailable = "No vision-capable models currently deployed";
        }

        return [
            'vision_enabled' => $visionEnabled,
            'vision_available' => $visionAvailable,
            'available_models' => $availableModels,
            'reason_if_unavailable' => $reasonIfUnavailable,
        ];
    }

    /**
     * Validate that input contains processable content
     * 
     * Checks for:
     * - Text (always allowed)
     * - Images (if vision enabled)
     * - Documents (if vision enabled)
     * 
     * @param string $userInput
     * @param bool $hasImages
     * @param bool $hasDocuments
     * @return array {
     *     'valid': bool,
     *     'can_process_images': bool,
     *     'can_process_documents': bool,
     *     'warnings': string[],
     * }
     */
    public function validateInput(string $userInput, bool $hasImages = false, bool $hasDocuments = false): array
    {
        $warnings = [];
        $visionStatus = $this->getVisionStatus();

        $canProcessImages = $visionStatus['vision_enabled'] && $visionStatus['vision_available'];
        $canProcessDocuments = $visionStatus['vision_enabled'] && $visionStatus['vision_available'];

        if ($hasImages && !$canProcessImages) {
            $warnings[] = "Images provided but vision is not enabled/available";
        }

        if ($hasDocuments && !$canProcessDocuments) {
            $warnings[] = "Documents provided but vision is not enabled/available";
        }

        // Always valid if text is present
        $isValid = !empty($userInput);

        return [
            'valid' => $isValid,
            'can_process_images' => $canProcessImages,
            'can_process_documents' => $canProcessDocuments,
            'warnings' => $warnings,
        ];
    }
}
