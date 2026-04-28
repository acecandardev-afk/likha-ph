<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $products = Product::where('approval_status', 'approved')->with('artisan')->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            $this->command->error('Please run UserSeeder and ProductSeeder first!');

            return;
        }

        $admin = User::query()->where('role', 'admin')->orderBy('id')->first();
        if (! $admin) {
            $this->command->error('No admin user found. Run UserSeeder first.');

            return;
        }

        // Sample order scenarios
        $orderScenarios = [
            [
                'customer_email' => 'juan.delacruz@example.com',
                'products' => [
                    ['name' => 'Bamboo Serving Tray', 'quantity' => 2],
                    ['name' => 'Bamboo Utensil Set', 'quantity' => 1],
                ],
                'status' => 'confirmed',
                'payment_status' => 'verified',
            ],
            [
                'customer_email' => 'anna.reyes@example.com',
                'products' => [
                    ['name' => 'Abaca Table Runner', 'quantity' => 1],
                    ['name' => 'Abaca Placemats Set', 'quantity' => 2],
                ],
                'status' => 'pending',
                'payment_status' => 'pending',
            ],
            [
                'customer_email' => 'michael.tan@example.com',
                'products' => [
                    ['name' => 'Small Storage Basket', 'quantity' => 3],
                ],
                'status' => 'completed',
                'payment_status' => 'verified',
            ],
        ];

        foreach ($orderScenarios as $scenario) {
            $customer = $customers->firstWhere('email', $scenario['customer_email']);

            if (! $customer) {
                continue;
            }

            // Group products by artisan
            $ordersByArtisan = [];

            foreach ($scenario['products'] as $productData) {
                $product = $products->firstWhere('name', $productData['name']);

                if (! $product) {
                    continue;
                }

                $artisanId = $product->artisan_id;

                if (! isset($ordersByArtisan[$artisanId])) {
                    $ordersByArtisan[$artisanId] = [];
                }

                $ordersByArtisan[$artisanId][] = [
                    'product' => $product,
                    'quantity' => $productData['quantity'],
                ];
            }

            // Create separate order for each artisan
            foreach ($ordersByArtisan as $artisanId => $items) {
                $subtotal = 0;

                foreach ($items as $item) {
                    $subtotal += $item['product']->price * $item['quantity'];
                }

                $order = Order::create([
                    'customer_id' => $customer->id,
                    'artisan_id' => $artisanId,
                    'subtotal' => $subtotal,
                    'platform_fee' => round($subtotal * (float) config('fees.platform_fee_rate', 0.05), 2),
                    'total' => $subtotal + round($subtotal * (float) config('fees.platform_fee_rate', 0.05), 2),
                    'status' => $scenario['status'],
                    'customer_notes' => 'Please handle with care.',
                ]);

                // Create order items
                foreach ($items as $item) {
                    $itemSubtotal = $item['product']->price * $item['quantity'];

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product']->id,
                        'product_name' => $item['product']->name,
                        'product_description' => $item['product']->description,
                        'price' => $item['product']->price,
                        'quantity' => $item['quantity'],
                        'subtotal' => $itemSubtotal,
                    ]);

                    // Reduce stock
                    $item['product']->decrement('stock', $item['quantity']);
                }

                // Create payment record
                Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => 'bank_transfer',
                    'amount' => $order->total,
                    'verification_status' => $scenario['payment_status'],
                    'verified_by' => $scenario['payment_status'] === 'verified' ? $admin->id : null,
                    'verified_at' => $scenario['payment_status'] === 'verified' ? now() : null,
                ]);

                $this->command->info("✓ Order created: {$order->order_number} - {$customer->name} (₱{$order->total})");
            }
        }

        $this->command->info('');
        $this->command->info('Sample orders created successfully!');
    }
}
