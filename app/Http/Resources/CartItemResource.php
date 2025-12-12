<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'variant_id' => $this->variant_id,
            'quantity' => (int) $this->quantity,
            'unit_price' => (float) ($this->unit_price ?? $this->product->effective_price ?? 0),
            'subtotal' => (float) $this->subtotal,
            'product' => new ProductResource($this->whenLoaded('product')),
            'variant' => new VariantResource($this->whenLoaded('variant')),
        ];
    }
}
