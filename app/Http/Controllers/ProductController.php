<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Customer;
use App\Models\Product;
use App\Support\Pricing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $tier = $this->tierFor($request);

        $products = Product::query()
            ->when($request->query('q'), function ($query, $q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
            })
            ->when($request->query('category'), function ($query, $category) {
                $query->where('category', $category);
            })
            ->with('variants')
            ->withSum('orderItems as units_sold', 'quantity')
            ->orderBy('name')
            ->get()
            ->each(fn (Product $product) => $this->applyTierPrice($product, $tier));

        return ProductResource::collection($products);
    }

    public function show(Request $request, Product $product): ProductResource
    {
        $product->load('variants');
        $this->applyTierPrice($product, $this->tierFor($request));

        return new ProductResource($product);
    }

    private function tierFor(Request $request): string
    {
        return Customer::find($request->query('customer_id'))?->tier ?? 'bronze';
    }

    private function applyTierPrice(Model $product, string $tier): void
    {
        $product->setAttribute('tier', $tier);
        $product->setAttribute('price_cents', Pricing::priceForTier($product->base_price_cents, $tier));
    }
}
