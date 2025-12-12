<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'price' => $this->price !== null ? (float) $this->price : null,
            'sale_price' => $this->sale_price !== null ? (float) $this->sale_price : null,
            'effective_price' => (float) $this->effective_price,
            'stock' => (int) $this->stock,
            'options' => $this->options,
            'image_url' => $this->image_path ? asset('storage/' . $this->image_path) : null,
            'status' => (bool) $this->status,
            'in_stock' => $this->stock > 0,
        ];
    }
}
