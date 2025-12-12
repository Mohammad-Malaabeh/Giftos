<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'original_filename' => $this->original_filename,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'formatted_size' => $this->formatted_size,
            'extension' => $this->extension,
            'path' => $this->path,
            'url' => $this->url,
            'thumbnail_url' => $this->when($this->thumbnail_url, $this->thumbnail_url),
            'disk' => $this->disk,
            'metadata' => $this->when($this->metadata, $this->metadata),
            'sort_order' => $this->sort_order,
            'is_primary' => $this->is_primary,
            
            // Type helpers
            'is_image' => $this->isImage(),
            'is_video' => $this->isVideo(),
            'is_document' => $this->isDocument(),
            
            // Relationships
            'mediable_type' => $this->when($this->mediable_type, $this->mediable_type),
            'mediable_id' => $this->when($this->mediable_id, $this->mediable_id),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // API URLs
            'api_url' => route('api.media.show', $this->id),
            'download_url' => route('api.media.download', $this->id),
        ];
    }
}
