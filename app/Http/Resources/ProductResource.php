<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $availability = [];
        $totalStock = 0;
        foreach ($this->variants as $variant) {
            $availability[] = [
                'variant_id' => $variant->id,
                'eu_size' => $variant->eu_size,
                'stock' => $variant->stock,
            ];
            $totalStock += $variant->stock;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'category' => $this->category,
            'colorway' => $this->colorway,
            'base_price_cents' => $this->base_price_cents,
            'price_cents' => $this->price_cents,
            'tier' => $this->tier,
            'total_stock' => $totalStock,
            'units_sold' => (int) ($this->units_sold ?? 0),
            'availability' => $availability,
        ];
    }
}
