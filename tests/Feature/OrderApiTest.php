<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeVariant(int $stock, int $price = 10000): ProductVariant
    {
        $product = Product::create([
            'name' => 'Test Boot',
            'sku' => 'TST-'.fake()->unique()->numerify('####'),
            'category' => 'FG',
            'colorway' => 'Black/White',
            'base_price_cents' => $price,
        ]);

        return ProductVariant::create([
            'product_id' => $product->id,
            'eu_size' => 42,
            'stock' => $stock,
        ]);
    }

    public function test_order_persists_items_and_decrements_stock(): void
    {
        $customer = Customer::create([
            'company_name' => 'Silver Co',
            'email' => 'silver@example.test',
            'tier' => 'silver',
        ]);

        $variant = $this->makeVariant(stock: 10, price: 10000);

        $response = $this->postJson('/api/orders', [
            'customer_id' => $customer->id,
            'lines' => [
                ['product_variant_id' => $variant->id, 'quantity' => 2],
            ],
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('orders', [
            'id' => $response->json('data.id'),
            'customer_id' => $customer->id,
            'status' => 'submitted',
        ]);

        // silver tier = 5% off 10000 = 9500 per unit, qty 2.
        $this->assertDatabaseHas('order_items', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price_cents' => 9500,
        ]);

        $this->assertSame(8, $variant->fresh()->stock);
    }

    public function test_order_rejects_lines_that_exceed_available_stock(): void
    {
        $customer = Customer::create([
            'company_name' => 'Bronze Co',
            'email' => 'bronze@example.test',
            'tier' => 'bronze',
        ]);

        $variant = $this->makeVariant(stock: 3);

        $response = $this->postJson('/api/orders', [
            'customer_id' => $customer->id,
            'lines' => [
                ['product_variant_id' => $variant->id, 'quantity' => 5],
            ],
        ]);

        $response->assertStatus(422);

        // Stock must be untouched and no order should have been persisted.
        $this->assertSame(3, $variant->fresh()->stock);
        $this->assertDatabaseCount('orders', 0);
    }
}
