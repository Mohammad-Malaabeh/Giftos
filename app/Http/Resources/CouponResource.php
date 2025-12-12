<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'value' => (float) $this->value,
            'max_discount' => $this->max_discount !== null ? (float) $this->max_discount : null,
            'usage_limit' => $this->usage_limit,
            'used_count' => (int) $this->used_count,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'active' => (bool) $this->active,
            'is_valid' => $this->isValidNow(),
        ];
    }
}
