<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;
use App\Http\Resources\MediaResource;
use App\Http\Resources\MediaCollection;

class MediaController extends Controller
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        'video/mp4',
        'video/avi',
        'video/mov',
        'video/wmv',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'text/csv',
    ];

    private const MAX_FILE_SIZES = [
        'image' => 10 * 1024 * 1024, // 10MB
        'video' => 100 * 1024 * 1024, // 100MB
        'document' => 20 * 1024 * 1024, // 20MB
        'default' => 10 * 1024 * 1024, // 10MB
    ];

    public function upload(Request $request): JsonResponse
    {
        $this->validate($request, [
            'file' => 'required|file|max:102400', // Max 100MB
            'folder' => 'nullable|string|max:255',
            'is_primary' => 'nullable|boolean',
            'mediable_type' => 'nullable|string|max:255',
            'mediable_id' => 'nullable|integer',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $file = $request->file('file');
        
        // Validate file type and size
        $this->validateFileTypeAndSize($file);

        // Determine storage disk
        $disk = $this->determineStorageDisk($file);

        // Generate filename and path
        $filename = $this->generateFilename($file);
        $folder = $request->get('folder', $this->getDefaultFolder($file));
        $path = $folder . '/' . $filename;

        // Store file
        $storedPath = Storage::disk($disk)->putFileAs($folder, $file, $filename, [
            'visibility' => 'public',
            'metadata' => [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => auth()->id(),
                'uploaded_at' => now()->toISOString(),
            ],
        ]);

        // Create media record
        $media = Media::create([
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $storedPath,
            'disk' => $disk,
            'metadata' => [
                'dimensions' => $this->getImageDimensions($file),
                'exif' => $this->getExifData($file),
                'color_profile' => $this->getColorProfile($file),
            ],
            'mediable_type' => $request->get('mediable_type'),
            'mediable_id' => $request->get('mediable_id'),
            'sort_order' => $request->get('sort_order', 0),
            'is_primary' => $request->boolean('is_primary', false),
        ]);

        // Process image optimizations
        if ($this->isImage($file)) {
            $this->processImageOptimizations($media, $disk);
        }

        return response()->json([
            'message' => 'File uploaded successfully',
            'data' => new MediaResource($media),
        ], 201);
    }

    public function uploadMultiple(Request $request): JsonResponse
    {
        $this->validate($request, [
            'files' => 'required|array|max:10', // Max 10 files
            'files.*' => 'required|file|max:102400', // Max 100MB per file
            'folder' => 'nullable|string|max:255',
            'mediable_type' => 'nullable|string|max:255',
            'mediable_id' => 'nullable|integer',
        ]);

        $uploadedFiles = [];
        $errors = [];

        foreach ($request->file('files') as $index => $file) {
            try {
                $this->validateFileTypeAndSize($file);
                
                $disk = $this->determineStorageDisk($file);
                $filename = $this->generateFilename($file);
                $folder = $request->get('folder', $this->getDefaultFolder($file));
                $path = $folder . '/' . $filename;

                $storedPath = Storage::disk($disk)->putFileAs($folder, $file, $filename, [
                    'visibility' => 'public',
                ]);

                $media = Media::create([
                    'filename' => $filename,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'path' => $storedPath,
                    'disk' => $disk,
                    'mediable_type' => $request->get('mediable_type'),
                    'mediable_id' => $request->get('mediable_id'),
                    'sort_order' => $index,
                    'is_primary' => $index === 0, // First file is primary
                ]);

                if ($this->isImage($file)) {
                    $this->processImageOptimizations($media, $disk);
                }

                $uploadedFiles[] = new MediaResource($media);
            } catch (\Exception $e) {
                $errors[] = [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'message' => count($uploadedFiles) . ' files uploaded successfully',
            'uploaded' => $uploadedFiles,
            'errors' => $errors,
        ], 201);
    }

    public function index(Request $request): MediaCollection
    {
        $query = Media::query()->with('mediable');

        // Filter by type
        if ($request->has('type')) {
            $type = $request->get('type');
            match ($type) {
                'image' => $query->where('mime_type', 'like', 'image/%'),
                'video' => $query->where('mime_type', 'like', 'video/%'),
                'document' => $query->where('mime_type', 'like', 'application/%'),
                default => null,
            };
        }

        // Filter by disk
        if ($request->has('disk')) {
            $query->where('disk', $request->get('disk'));
        }

        // Filter by mediable
        if ($request->has('mediable_type') && $request->has('mediable_id')) {
            $query->where('mediable_type', $request->get('mediable_type'))
                  ->where('mediable_id', $request->get('mediable_id'));
        }

        // Search
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('filename', 'like', '%' . $request->get('search') . '%')
                  ->orWhere('original_filename', 'like', '%' . $request->get('search') . '%');
            });
        }

        // Sort
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($sort, $order);

        $perPage = min($request->get('per_page', 15), 100);
        $media = $query->paginate($perPage);

        return new MediaCollection($media);
    }

    public function show(Media $media): MediaResource
    {
        $media->load('mediable');
        return new MediaResource($media);
    }

    public function update(Request $request, Media $media): JsonResponse
    {
        $this->validate($request, [
            'sort_order' => 'nullable|integer|min:0',
            'is_primary' => 'nullable|boolean',
            'metadata' => 'nullable|array',
        ]);

        $media->update($request->only(['sort_order', 'is_primary', 'metadata']));

        return response()->json([
            'message' => 'Media updated successfully',
            'data' => new MediaResource($media),
        ]);
    }

    public function destroy(Media $media): JsonResponse
    {
        // Delete from storage
        Storage::disk($media->disk)->delete($media->path);

        // Delete thumbnails if image
        if ($media->isImage()) {
            $thumbnailPath = $media->getThumbnailPath();
            Storage::disk($media->disk)->delete($thumbnailPath);
        }

        // Delete record
        $media->delete();

        return response()->json([
            'message' => 'Media deleted successfully',
        ]);
    }

    public function download(Media $media): JsonResponse
    {
        // Check if user has permission to download
        $this->authorize('download', $media);

        // Generate signed URL for secure download
        $url = Storage::disk($media->disk)->temporaryUrl(
            $media->path,
            now()->addMinutes(15)
        );

        return response()->json([
            'download_url' => $url,
            'filename' => $media->original_filename,
            'expires_at' => now()->addMinutes(15)->toISOString(),
        ]);
    }

    public function signedUrl(Media $media, Request $request): JsonResponse
    {
        $expiresIn = min($request->get('expires_in', 60), 3600); // Max 1 hour
        
        $url = Storage::disk($media->disk)->temporaryUrl(
            $media->path,
            now()->addMinutes($expiresIn)
        );

        return response()->json([
            'signed_url' => $url,
            'expires_at' => now()->addMinutes($expiresIn)->toISOString(),
        ]);
    }

    private function validateFileTypeAndSize(UploadedFile $file): void
    {
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        // Check mime type
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            throw ValidationException::withMessages([
                'file' => 'File type not allowed. Allowed types: ' . implode(', ', self::ALLOWED_MIME_TYPES),
            ]);
        }

        // Check file size
        $maxSize = $this->getMaxFileSize($mimeType);
        if ($size > $maxSize) {
            $maxSizeMB = round($maxSize / 1024 / 1024, 2);
            throw ValidationException::withMessages([
                'file' => "File size too large. Maximum size for this file type is {$maxSizeMB}MB",
            ]);
        }
    }

    private function getMaxFileSize(string $mimeType): int
    {
        if (str_starts_with($mimeType, 'image/')) {
            return self::MAX_FILE_SIZES['image'];
        } elseif (str_starts_with($mimeType, 'video/')) {
            return self::MAX_FILE_SIZES['video'];
        } elseif (str_starts_with($mimeType, 'application/')) {
            return self::MAX_FILE_SIZES['document'];
        }

        return self::MAX_FILE_SIZES['default'];
    }

    private function determineStorageDisk(UploadedFile $file): string
    {
        // Use S3 for production, local for development
        if (config('filesystems.default') === 's3') {
            return 's3';
        }

        return 'public';
    }

    private function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $timestamp = now()->format('YmdHis');
        $random = mt_rand(1000, 9999);

        return "{$basename}-{$timestamp}-{$random}.{$extension}";
    }

    private function getDefaultFolder(UploadedFile $file): string
    {
        if (str_starts_with($file->getMimeType(), 'image/')) {
            return 'images/' . date('Y/m');
        } elseif (str_starts_with($file->getMimeType(), 'video/')) {
            return 'videos/' . date('Y/m');
        } elseif (str_starts_with($file->getMimeType(), 'application/')) {
            return 'documents/' . date('Y/m');
        }

        return 'uploads/' . date('Y/m');
    }

    private function isImage(UploadedFile $file): bool
    {
        return str_starts_with($file->getMimeType(), 'image/');
    }

    private function getImageDimensions(UploadedFile $file): ?array
    {
        if (!$this->isImage($file)) {
            return null;
        }

        try {
            $imageInfo = getimagesize($file->getPathname());
            if ($imageInfo) {
                return [
                    'width' => $imageInfo[0],
                    'height' => $imageInfo[1],
                    'type' => $imageInfo[2],
                ];
            }
        } catch (\Exception $e) {
            // Ignore errors
        }

        return null;
    }

    private function getExifData(UploadedFile $file): ?array
    {
        if (!$this->isImage($file) || $file->getMimeType() === 'image/svg+xml') {
            return null;
        }

        try {
            $exif = exif_read_data($file->getPathname());
            if ($exif) {
                return array_filter($exif, function ($value) {
                    return !is_array($value);
                });
            }
        } catch (\Exception $e) {
            // Ignore errors
        }

        return null;
    }

    private function getColorProfile(UploadedFile $file): ?string
    {
        if (!$this->isImage($file)) {
            return null;
        }

        try {
            $imageInfo = getimagesize($file->getPathname());
            if ($imageInfo && isset($imageInfo['channels'])) {
                return $imageInfo['channels'] === 3 ? 'RGB' : 'CMYK';
            }
        } catch (\Exception $e) {
            // Ignore errors
        }

        return null;
    }

    private function processImageOptimizations(Media $media, string $disk): void
    {
        // This would typically use Intervention Image or similar
        // For now, just create a placeholder thumbnail
        try {
            $imagePath = Storage::disk($disk)->path($media->path);
            $thumbnailPath = $media->getThumbnailPath();
            $thumbnailDir = dirname(Storage::disk($disk)->path($thumbnailPath));

            // Ensure thumbnail directory exists
            if (!is_dir($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }

            // Create thumbnail (placeholder logic)
            if (function_exists('imagecreatetruecolor')) {
                $source = imagecreatefromstring(Storage::disk($disk)->get($media->path));
                if ($source) {
                    $width = imagesx($source);
                    $height = imagesy($source);
                    $thumbWidth = min(300, $width);
                    $thumbHeight = min(300, $height);

                    $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
                    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);

                    // Save thumbnail
                    $extension = pathinfo($media->filename, PATHINFO_EXTENSION);
                    match (strtolower($extension)) {
                        'jpeg', 'jpg' => imagejpeg($thumb, Storage::disk($disk)->path($thumbnailPath), 80),
                        'png' => imagepng($thumb, Storage::disk($disk)->path($thumbnailPath), 8),
                        'gif' => imagegif($thumb, Storage::disk($disk)->path($thumbnailPath)),
                        default => null,
                    };

                    imagedestroy($source);
                    imagedestroy($thumb);
                }
            }
        } catch (\Exception $e) {
            // Log error but don't fail the upload
            \Log::error('Failed to create thumbnail: ' . $e->getMessage());
        }
    }
}
