<?php

namespace Modules\TemplateEngine\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Store an uploaded file
     *
     * @param UploadedFile $file
     * @param string $fieldName
     * @param int|null $submissionId
     * @return string
     */
    public function storeUploadedFile(UploadedFile $file, string $fieldName, ?int $submissionId = null): string
    {
        $fileName = $this->generateUniqueFileName($file, $fieldName);
        
        $directory = 'template-images/submissions';
        if ($submissionId) {
            $directory .= '/' . $submissionId;
        } else {
            // For temporary storage before submission is created
            $directory .= '/temp';
        }
        
        return $file->storeAs($directory, $fileName, 'public');
    }

    /**
     * Store uploaded file with specific directory
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string $fieldName
     * @return string
     */
    public function storeFileInDirectory(UploadedFile $file, string $directory, string $fieldName): string
    {
        $fileName = $this->generateUniqueFileName($file, $fieldName);
        return $file->storeAs($directory, $fileName, 'public');
    }

    /**
     * Delete a file from storage
     *
     * @param string $path
     * @return bool
     */
    public function deleteFile(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        return false;
    }

    /**
     * Delete all files in a directory
     *
     * @param string $directory
     * @return bool
     */
    public function deleteDirectory(string $directory): bool
    {
        if (Storage::disk('public')->exists($directory)) {
            return Storage::disk('public')->deleteDirectory($directory);
        }
        return false;
    }

    /**
     * Get public URL for a file
     *
     * @param string $path
     * @return string
     */
    public function getFileUrl(string $path): string
    {
        return asset('storage/' . $path);
    }

    /**
     * Validate file type against allowed types
     *
     * @param UploadedFile $file
     * @param array $allowedTypes
     * @return bool
     */
    public function validateFileType(UploadedFile $file, array $allowedTypes): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        return in_array($extension, array_map('strtolower', $allowedTypes));
    }

    /**
     * Generate unique file name
     *
     * @param UploadedFile $file
     * @param string $fieldName
     * @return string
     */
    public function generateUniqueFileName(UploadedFile $file, string $fieldName): string
    {
        $extension = $file->getClientOriginalExtension();
        $safeName = Str::slug($fieldName, '_');
        $timestamp = time();
        $uniqueId = Str::random(8);
        
        return "{$safeName}_{$timestamp}_{$uniqueId}.{$extension}";
    }

    /**
     * Move temporary files to permanent location after submission is created
     *
     * @param array $filePaths
     * @param int $submissionId
     * @return array Updated file paths
     */
    public function moveTemporaryFiles(array $filePaths, int $submissionId): array
    {
        $updatedPaths = [];
        
        foreach ($filePaths as $fieldName => $path) {
            if (strpos($path, 'temp/') !== false && Storage::disk('public')->exists($path)) {
                // Get the file name
                $fileName = basename($path);
                
                // New path
                $newPath = "template-images/submissions/{$submissionId}/{$fileName}";
                
                // Move the file
                Storage::disk('public')->move($path, $newPath);
                
                $updatedPaths[$fieldName] = $newPath;
            } else {
                $updatedPaths[$fieldName] = $path;
            }
        }
        
        return $updatedPaths;
    }

    /**
     * Clean up temporary files older than specified hours
     *
     * @param int $hours
     * @return int Number of files cleaned
     */
    public function cleanupTemporaryFiles(int $hours = 24): int
    {
        $tempDirectory = 'template-images/submissions/temp';
        $count = 0;
        
        if (!Storage::disk('public')->exists($tempDirectory)) {
            return 0;
        }
        
        $files = Storage::disk('public')->files($tempDirectory);
        $cutoffTime = time() - ($hours * 3600);
        
        foreach ($files as $file) {
            $lastModified = Storage::disk('public')->lastModified($file);
            
            if ($lastModified < $cutoffTime) {
                Storage::disk('public')->delete($file);
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Validate image dimensions
     *
     * @param UploadedFile $file
     * @param int|null $maxWidth
     * @param int|null $maxHeight
     * @return bool
     */
    public function validateImageDimensions(UploadedFile $file, ?int $maxWidth = null, ?int $maxHeight = null): bool
    {
        if (!$maxWidth && !$maxHeight) {
            return true;
        }
        
        try {
            $imageSize = getimagesize($file->getRealPath());
            
            if ($imageSize === false) {
                return false;
            }
            
            list($width, $height) = $imageSize;
            
            if ($maxWidth && $width > $maxWidth) {
                return false;
            }
            
            if ($maxHeight && $height > $maxHeight) {
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

