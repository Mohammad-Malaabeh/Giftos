<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MediaCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->total(),
                'count' => $this->count(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage(),
                'has_more_pages' => $this->hasMorePages(),
                'first_page_url' => $this->url(1),
                'last_page_url' => $this->url($this->lastPage()),
                'next_page_url' => $this->nextPageUrl(),
                'prev_page_url' => $this->previousPageUrl(),
            ],
            'links' => [
                'self' => $request->fullUrl(),
                'first' => $this->url(1),
                'last' => $this->url($this->lastPage()),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
            ],
            'filters' => $this->when($request->hasAny(['type', 'disk', 'search', 'mediable_type', 'mediable_id']), [
                'type' => $request->get('type'),
                'disk' => $request->get('disk'),
                'search' => $request->get('search'),
                'mediable_type' => $request->get('mediable_type'),
                'mediable_id' => $request->get('mediable_id'),
                'sort' => $request->get('sort', 'created_at'),
                'order' => $request->get('order', 'desc'),
            ]),
            'aggregates' => $this->when($this->collection->isNotEmpty(), [
                'total_size' => $this->collection->sum('size'),
                'formatted_total_size' => $this->formatBytes($this->collection->sum('size')),
                'images_count' => $this->collection->filter(fn($media) => str_starts_with($media->mime_type, 'image/'))->count(),
                'videos_count' => $this->collection->filter(fn($media) => str_starts_with($media->mime_type, 'video/'))->count(),
                'documents_count' => $this->collection->filter(fn($media) => str_starts_with($media->mime_type, 'application/'))->count(),
                'primary_count' => $this->collection->where('is_primary', true)->count(),
                'average_size' => $this->formatBytes($this->collection->avg('size')),
                'largest_file' => $this->collection->max('size'),
                'smallest_file' => $this->collection->min('size'),
            ]),
            'storage_info' => [
                'disk_usage' => $this->getDiskUsage(),
                'available_disks' => config('filesystems.disks') ? array_keys(config('filesystems.disks')) : [],
            ],
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

    private function getDiskUsage(): array
    {
        $usage = [];
        
        foreach (config('filesystems.disks', []) as $disk => $config) {
            try {
                $files = \Illuminate\Support\Facades\Storage::disk($disk)->allFiles();
                $totalSize = 0;
                $fileCount = count($files);
                
                foreach ($files as $file) {
                    $totalSize += \Illuminate\Support\Facades\Storage::disk($disk)->size($file);
                }
                
                $usage[$disk] = [
                    'file_count' => $fileCount,
                    'total_size' => $totalSize,
                    'formatted_size' => $this->formatBytes($totalSize),
                ];
            } catch (\Exception $e) {
                $usage[$disk] = [
                    'error' => 'Unable to access disk',
                ];
            }
        }
        
        return $usage;
    }
}
