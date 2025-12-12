<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCollection extends ResourceCollection
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
            'filters' => $this->when($request->hasAny(['search', 'category', 'price_min', 'price_max', 'featured', 'on_sale']), [
                'search' => $request->get('search'),
                'category' => $request->get('category'),
                'price_min' => $request->get('price_min'),
                'price_max' => $request->get('price_max'),
                'featured' => $request->boolean('featured'),
                'on_sale' => $request->boolean('on_sale'),
                'sort' => $request->get('sort', 'created_at'),
                'order' => $request->get('order', 'desc'),
            ]),
            'aggregates' => $this->when($this->collection->isNotEmpty(), [
                'min_price' => $this->collection->min('price'),
                'max_price' => $this->collection->max('price'),
                'avg_price' => round((float) $this->collection->avg('price'), 2),
                'total_stock' => $this->collection->sum('stock'),
                'featured_count' => $this->collection->where('featured', true)->count(),
                'on_sale_count' => $this->collection->where('sale_price', '>', 0)->count(),
            ]),
        ];
    }
}
