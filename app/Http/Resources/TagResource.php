<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->when($this->description, $this->description),
            'color' => $this->when($this->color, $this->color),
            'is_active' => $this->is_active,
            'usage_count' => $this->usage_count,
            
            // Relationships counts
            'products_count' => $this->when($this->relationLoaded('products'), $this->products->count()),
            'orders_count' => $this->when($this->relationLoaded('orders'), $this->orders->count()),
            'users_count' => $this->when($this->relationLoaded('users'), $this->users->count()),
            
            // Related items (when loaded)
            'products' => $this->when($this->relationLoaded('products'), function () {
                return ProductResource::collection($this->products);
            }),
            'orders' => $this->when($this->relationLoaded('orders'), function () {
                return OrderResource::collection($this->orders);
            }),
            'users' => $this->when($this->relationLoaded('users'), function () {
                return UserResource::collection($this->users);
            }),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // URLs
            'url' => route('tags.show', $this->slug),
            'api_url' => route('api.tags.show', $this->id),
            
            // Permissions
            'can_delete' => $this->when(auth()->check(), function () {
                return auth()->user()->isAdmin() && $this->canBeDeleted();
            }),
            'can_edit' => $this->when(auth()->check(), function () {
                return auth()->user()->isAdmin();
            }),
        ];
    }
}
