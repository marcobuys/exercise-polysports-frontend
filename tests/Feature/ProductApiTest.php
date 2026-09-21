<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_endpoint_returns_tier_adjusted_price(): void
    {
        $customer = Customer::create([
            'company_name' => 'Gold Co',
            'email' => 'gold@example.test',
            'tier' => 'gold',
        ]);

        $product = Product::create([
            'name' => 'Test Boot',
            'sku' => 'TST-0001',
            'category' => 'FG',
            'colorway' => 'Black/White',
            'base_price_cents' => 10000,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'eu_size' => 42,
            'stock' => 5,
        ]);

        $response = $this->getJson("/api/products?customer_id={$customer->id}");

        $response->assertOk();
        $response->assertJsonPath('data.0.price_cents', 9000);
        $response->assertJsonPath('data.0.base_price_cents', 10000);
        $response->assertJsonPath('data.0.tier', 'gold');
    }

    public function test_products_endpoint_query_count_is_bounded(): void
    {
        $customer = Customer::create([
            'company_name' => 'Bronze Co',
            'email' => 'bronze@example.test',
            'tier' => 'bronze',
        ]);

        for ($p = 0; $p < 5; $p++) {
            $product = Product::create([
                'name' => "Boot {$p}",
                'sku' => "SKU-000{$p}",
                'category' => 'FG',
                'colorway' => 'Red/Black',
                'base_price_cents' => 12000,
            ]);

            foreach (range(39, 46) as $size) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'eu_size' => $size,
                    'stock' => 10,
                ]);
            }
        }

        $queries = 0;
        DB::listen(function () use (&$queries) {
            $queries++;
        });

        $response = $this->getJson("/api/products?customer_id={$customer->id}");

        $response->assertOk();
        $response->assertJsonCount(5, 'data');

        $this->assertLessThan(
            6,
            $queries,
            "The products listing ran {$queries} queries; it should stay well under 6 regardless of how many products exist."
        );
    }
}
