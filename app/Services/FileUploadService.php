<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    public function uploadProfilePicture(UploadedFile $file, int $userId): string
    {
        $filename = "profile_{$userId}_" . time() . "." . $file->getClientOriginalExtension();
        $path = $file->storeAs('profiles', $filename, 'public');

        return Storage::url($path);
    }

    public function uploadMessageAttachment(UploadedFile $file, int $messageId): string
    {
        $filename = "message_{$messageId}_" . time() . "." . $file->getClientOriginalExtension();
        $path = $file->storeAs('messages', $filename, 'public');

        return Storage::url($path);
    }

    public function uploadFamilyDocument(UploadedFile $file, int $familyId): string
    {
        $filename = "family_{$familyId}_" . time() . "." . $file->getClientOriginalExtension();
        $path = $file->storeAs('families', $filename, 'public');

        return Storage::url($path);
    }

    public function uploadImage(UploadedFile $file, string $directory = 'images'): string
    {
        $filename = Str::random(40) . "." . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, 'public');

        return Storage::url($path);
    }

    public function deleteFile(string $filePath): bool
    {
        $relativePath = str_replace('/storage/', '', $filePath);
        return Storage::disk('public')->delete($relativePath);
    }

    public function getFileSize(string $filePath): int
    {
        $relativePath = str_replace('/storage/', '', $filePath);
        return Storage::disk('public')->size($relativePath);
    }

    public function getFileUrl(string $filePath): string
    {
        return Storage::url($filePath);
    }

    public function validateImage(UploadedFile $file): bool
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        return in_array($file->getMimeType(), $allowedTypes) && $file->getSize() <= $maxSize;
    }

    public function validateDocument(UploadedFile $file): bool
    {
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain'
        ];
        $maxSize = 10 * 1024 * 1024; // 10MB

        return in_array($file->getMimeType(), $allowedTypes) && $file->getSize() <= $maxSize;
    }

    public function resizeImage(string $filePath, int $width, int $height): string
    {
        // Logique pour redimensionner les images
        // Utiliser Intervention Image ou une autre bibliothèque
        return $filePath;
    }

    public function generateThumbnail(string $filePath, int $width = 150, int $height = 150): string
    {
        // Logique pour générer des miniatures
        return $filePath;
    }

    public function getStorageInfo(): array
    {
        $totalSpace = disk_total_space(storage_path('app/public'));
        $freeSpace = disk_free_space(storage_path('app/public'));
        $usedSpace = $totalSpace - $freeSpace;

        return [
            'total' => $this->formatBytes($totalSpace),
            'used' => $this->formatBytes($usedSpace),
            'free' => $this->formatBytes($freeSpace),
            'percentage' => round(($usedSpace / $totalSpace) * 100, 2),
        ];
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
