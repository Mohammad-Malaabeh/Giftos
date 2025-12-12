<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->title,
            'title' => $this->title,
            'slug' => $this->slug,
            'image_path' => $this->image_path,
            'description' => $this->description,
            'short_description' => $this->when($this->short_description, $this->short_description),
            'price' => $this->price,
            'formatted_price' => $this->formatted_price,
            'sale_price' => $this->when($this->sale_price, $this->sale_price),
            'formatted_sale_price' => $this->when($this->sale_price, $this->formatted_sale_price),
            'effective_price' => $this->effective_price,
            'discount_percentage' => $this->when($this->discount_percentage, $this->discount_percentage),
            'sku' => $this->sku,
            'barcode' => $this->when($this->barcode, $this->barcode),
            'weight' => $this->when($this->weight, $this->weight),
            'dimensions' => $this->when($this->dimensions, $this->dimensions),
            'stock' => $this->stock,
            'stock_status' => $this->stock_status,
            'stock_status_text' => $this->stock_status_text,
            'track_inventory' => $this->when($this->track_inventory, $this->track_inventory),
            'backorder_allowed' => $this->when($this->backorder_allowed, $this->backorder_allowed),
            'backorder_eta' => $this->when($this->backorder_eta, $this->backorder_eta),
            'requires_shipping' => $this->when($this->requires_shipping, $this->requires_shipping),
            'status' => $this->status,
            'featured' => $this->featured,
            'is_on_sale' => $this->is_on_sale,
            'has_variants' => $this->has_variants,
            'views' => $this->when($this->views, $this->formatted_views),

            // Media relationships
            'main_image' => $this->when($this->relationLoaded('primaryMedia'), function () {
                return new MediaResource($this->primaryMedia);
            }),
            'gallery' => $this->when($this->relationLoaded('media'), function () {
                return MediaResource::collection($this->media);
            }),
            'images' => $this->when($this->relationLoaded('images'), function () {
                return MediaResource::collection($this->images);
            }),
            'videos' => $this->when($this->relationLoaded('videos'), function () {
                return MediaResource::collection($this->videos);
            }),
            'documents' => $this->when($this->relationLoaded('documents'), function () {
                return MediaResource::collection($this->documents);
            }),

            // Legacy image support
            'image_url' => $this->when($this->image_path, $this->main_image_url),
            'image' => $this->main_image_url,
            'image_gallery' => $this->when($this->gallery, $this->gallery),

            // Relationships
            'category' => $this->when($this->relationLoaded('category'), function () {
                return new CategoryResource($this->category);
            }),
            'variants' => $this->when($this->relationLoaded('variants'), function () {
                return VariantResource::collection($this->variants);
            }),
            'reviews' => $this->when($this->relationLoaded('reviews'), function () {
                return ReviewResource::collection($this->reviews);
            }),
            'comments' => $this->when($this->relationLoaded('comments'), function () {
                return CommentResource::collection($this->comments);
            }),
            'tags' => $this->when($this->relationLoaded('tags'), function () {
                return TagResource::collection($this->tags);
            }),

            // Review statistics
            'reviews_count' => $this->whenCounted('reviews'),
            'avg_rating' => $this->when(isset($this->avg_rating), (float) $this->avg_rating),
            'reviews_summary' => $this->when($this->relationLoaded('approvedReviews'), function () {
                return [
                    '5_star' => $this->approvedReviews()->where('rating', 5)->count(),
                    '4_star' => $this->approvedReviews()->where('rating', 4)->count(),
                    '3_star' => $this->approvedReviews()->where('rating', 3)->count(),
                    '2_star' => $this->approvedReviews()->where('rating', 2)->count(),
                    '1_star' => $this->approvedReviews()->where('rating', 1)->count(),
                ];
            }),

            // SEO and metadata
            'meta_title' => $this->when($this->meta_title, $this->meta_title),
            'meta_description' => $this->when($this->meta_description, $this->meta_description),
            'meta_keywords' => $this->when($this->meta_keywords, $this->meta_keywords),

            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'published_at' => $this->when($this->published_at, $this->published_at),

            // Additional computed attributes
            'url' => route('product.show', $this->slug),
            'api_url' => route('api.products.show', $this->slug),
        ];
    }
}
