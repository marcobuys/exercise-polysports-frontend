<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (Customer::query()->exists()) {
            return;
        }

        $customers = $this->seedCustomers();
        $products = $this->seedProducts();
        $this->seedHistoricalOrders($customers, $products);
    }

    private function seedCustomers(): array
    {
        $rows = [
            ['FC Lowlands BV', 'orders@fclowlands.example', 'gold'],
            ['Northgate Athletic', 'buying@northgate.example', 'gold'],
            ['Pitchside Retail', 'wholesale@pitchside.example', 'silver'],
            ['Corner Flag Sports', 'team@cornerflag.example', 'silver'],
            ['Halfway Line Stores', 'purchasing@halfwayline.example', 'silver'],
            ['Boot Room Direct', 'hello@bootroom.example', 'bronze'],
            ['Grassroots Kit Co', 'kit@grassroots.example', 'bronze'],
            ['Touchline Traders', 'sales@touchline.example', 'bronze'],
            ['Penalty Box Supplies', 'orders@penaltybox.example', 'silver'],
            ['Extra Time Outfitters', 'buy@extratime.example', 'gold'],
        ];

        $customers = [];
        foreach ($rows as [$name, $email, $tier]) {
            $customers[] = Customer::create([
                'company_name' => $name,
                'email' => $email,
                'tier' => $tier,
            ]);
        }

        return $customers;
    }

    private function seedProducts(): array
    {
        $rows = [
            ['Vortex Strike', 'VTX-0001', 'FG', 'Black/Volt', 17900],
            ['Apex Glide', 'APX-0002', 'FG', 'White/Crimson', 21900],
            ['Tempo Control', 'TMP-0003', 'AG', 'Navy/Silver', 14900],
            ['Falcon Speed', 'FLC-0004', 'AG', 'Royal/Orange', 19900],
            ['Halo Touch', 'HAL-0005', 'IN', 'Grey/Lime', 9900],
            ['Drift Court', 'DRF-0006', 'IN', 'Teal/White', 8900],
            ['Summit Pro', 'SMT-0007', 'FG', 'Red/Black', 23900],
            ['Comet Lite', 'CMT-0008', 'AG', 'Yellow/Charcoal', 12900],
        ];

        $products = [];
        foreach ($rows as $i => [$name, $sku, $category, $colorway, $price]) {
            $product = Product::create([
                'name' => $name,
                'sku' => $sku,
                'category' => $category,
                'colorway' => $colorway,
                'base_price_cents' => $price,
            ]);

            foreach (range(39, 46) as $size) {
                // Leave some sizes low or sold out so availability looks realistic.
                $stock = match (true) {
                    ($size + $i) % 7 === 0 => 0,
                    ($size + $i) % 5 === 0 => random_int(1, 3),
                    default => random_int(8, 40),
                };

                ProductVariant::create([
                    'product_id' => $product->id,
                    'eu_size' => $size,
                    'stock' => $stock,
                ]);
            }

            $products[] = $product;
        }

        return $products;
    }

    private function seedHistoricalOrders(array $customers, array $products): void
    {
        $discount = ['bronze' => 0, 'silver' => 5, 'gold' => 10];

        foreach ($customers as $customer) {
            $orderCount = random_int(1, 3);

            for ($o = 0; $o < $orderCount; $o++) {
                $order = Order::create([
                    'customer_id' => $customer->id,
                    'status' => 'submitted',
                    'subtotal_cents' => 0,
                    'placed_at' => now()->subDays(random_int(3, 120)),
                ]);

                $subtotal = 0;
                $lineCount = random_int(1, 4);

                for ($l = 0; $l < $lineCount; $l++) {
                    $product = $products[array_rand($products)];
                    $variant = $product->variants()->inRandomOrder()->first();
                    $quantity = random_int(2, 12);
                    $unit = (int) round($product->base_price_cents * (1 - $discount[$customer->tier] / 100));

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_variant_id' => $variant->id,
                        'quantity' => $quantity,
                        'unit_price_cents' => $unit,
                    ]);

                    $subtotal += $unit * $quantity;
                }

                $order->update(['subtotal_cents' => $subtotal]);
            }
        }
    }
}
