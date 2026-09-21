<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Support\Pricing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $customer = Customer::findOrFail($data['customer_id']);

        $order = DB::transaction(function () use ($data, $customer) {
            $order = Order::create([
                'customer_id' => $customer->id,
                'status' => 'submitted',
                'subtotal_cents' => 0,
                'placed_at' => now(),
            ]);

            $subtotal = 0;

            foreach ($data['lines'] as $line) {
                $variant = ProductVariant::with('product')->findOrFail($line['product_variant_id']);
                $quantity = $line['quantity'];

                // Atomic conditional decrement: only succeeds if enough stock remains,
                // so concurrent checkouts can't oversell. Zero affected rows = insufficient.
                $affected = ProductVariant::query()
                    ->where('id', $variant->id)
                    ->where('stock', '>=', $quantity)
                    ->decrement('stock', $quantity);

                if ($affected === 0) {
                    throw ValidationException::withMessages([
                        'lines' => "Insufficient stock for {$variant->product->name} (EU {$variant->eu_size}).",
                    ]);
                }

                $unitPrice = Pricing::priceForTier($variant->product->base_price_cents, $customer->tier);

                $order->items()->create([
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'unit_price_cents' => $unitPrice,
                ]);

                $subtotal += $unitPrice * $quantity;
            }

            $order->update(['subtotal_cents' => $subtotal]);

            return $order;
        });

        return response()->json([
            'data' => [
                'id' => $order->id,
                'status' => $order->status,
                'subtotal_cents' => $order->subtotal_cents,
                'placed_at' => $order->placed_at,
                'item_count' => $order->items()->count(),
            ],
        ], 201);
    }
}
