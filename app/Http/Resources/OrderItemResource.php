<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product ? $this->product->title : 'Unknown Product',
            'variant_id' => $this->variant_id,
            'variant_sku' => $this->variant ? $this->variant->sku : null,
            'quantity' => (int) $this->quantity,
            'price' => (float) $this->price,
            'total' => (float) $this->total,
            'product' => new ProductResource($this->whenLoaded('product')),
            'variant' => new VariantResource($this->whenLoaded('variant')),
        ];
    }
}
