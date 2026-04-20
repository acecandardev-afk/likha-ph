<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductApproval;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $artisans = User::where('role', 'artisan')->get();
        $categories = Category::all();

        if ($artisans->isEmpty() || $categories->isEmpty()) {
            $this->command->error('Please run UserSeeder and CategorySeeder first!');
            return;
        }

        $admin = User::query()->where('role', 'admin')->orderBy('id')->first();
        if (! $admin) {
            $this->command->error('No admin user found. Run UserSeeder first (creates admin@guihulngan-handicrafts.local).');
            return;
        }

        // Products data structure: [artisan_email => [products]]
        $productsData = [
            'maria.santos@example.com' => [
                [
                    'category' => 'Bamboo Crafts',
                    'name' => 'Bamboo Serving Tray',
                    'description' => 'Beautiful handcrafted bamboo serving tray, perfect for breakfast in bed or entertaining guests. Features smooth finish and sturdy construction. Dimensions: 45cm x 30cm x 5cm.',
                    'price' => 850.00,
                    'stock' => 15,
                    'approval_status' => 'approved',
                ],
                [
                    'category' => 'Bamboo Crafts',
                    'name' => 'Bamboo Utensil Set',
                    'description' => 'Eco-friendly bamboo utensil set including spoons, forks, and chopsticks. Lightweight, durable, and perfect for daily use. Set of 6 pieces.',
                    'price' => 450.00,
                    'stock' => 25,
                    'approval_status' => 'approved',
                ],
                [
                    'category' => 'Home Decor',
                    'name' => 'Bamboo Wind Chimes',
                    'description' => 'Soothing bamboo wind chimes that create gentle melodies in the breeze. Hand-tuned for harmonious sounds. Length: 60cm.',
                    'price' => 650.00,
                    'stock' => 10,
                    'approval_status' => 'pending',
                ],
            ],
            'pedro.reyes@example.com' => [
                [
                    'category' => 'Woven Abaca',
                    'name' => 'Abaca Table Runner',
                    'description' => 'Elegant handwoven abaca table runner with traditional Filipino patterns. Adds natural sophistication to any dining table. Size: 150cm x 35cm.',
                    'price' => 1200.00,
                    'stock' => 8,
                    'approval_status' => 'approved',
                ],
                [
                    'category' => 'Woven Abaca',
                    'name' => 'Abaca Placemats Set',
                    'description' => 'Set of 4 handwoven abaca placemats. Heat-resistant and easy to clean. Each mat measures 40cm x 30cm.',
                    'price' => 800.00,
                    'stock' => 20,
                    'approval_status' => 'approved',
                ],
                [
                    'category' => 'Handwoven Bags',
                    'name' => 'Abaca Shopping Tote',
                    'description' => 'Durable and stylish abaca shopping bag with reinforced handles. Perfect alternative to plastic bags. Capacity: 15 liters.',
                    'price' => 950.00,
                    'stock' => 12,
                    'approval_status' => 'approved',
                ],
            ],
            'luz.garcia@example.com' => [
                [
                    'category' => 'Native Baskets',
                    'name' => 'Small Storage Basket',
                    'description' => 'Charming small basket perfect for organizing keys, jewelry, or office supplies. Handwoven with intricate patterns. Diameter: 20cm, Height: 15cm.',
                    'price' => 350.00,
                    'stock' => 30,
                    'approval_status' => 'approved',
                ],
                [
                    'category' => 'Native Baskets',
                    'name' => 'Large Laundry Basket',
                    'description' => 'Sturdy and spacious laundry basket with handles. Made from durable natural fibers. Diameter: 50cm, Height: 60cm.',
                    'price' => 1500.00,
                    'stock' => 5,
                    'approval_status' => 'approved',
                ],
                [
                    'category' => 'Native Baskets',
                    'name' => 'Fruit Basket',
                    'description' => 'Elegant fruit basket that doubles as kitchen decor. Woven with tight patterns to prevent fruit bruising. Diameter: 30cm, Height: 12cm.',
                    'price' => 550.00,
                    'stock' => 18,
                    'approval_status' => 'approved',
                ],
                [
                    'category' => 'Native Baskets',
                    'name' => 'Wall Hanging Basket',
                    'description' => 'Decorative wall basket for plants or storage. Includes hanging loop. Diameter: 25cm, Depth: 15cm.',
                    'price' => 480.00,
                    'stock' => 22,
                    'approval_status' => 'pending',
                ],
            ],
            'roberto.cruz@example.com' => [
                [
                    'category' => 'Handwoven Bags',
                    'name' => 'Beach Bag',
                    'description' => 'Large woven beach bag with tropical patterns. Water-resistant inner lining. Perfect for beach outings. Size: 45cm x 35cm x 15cm.',
                    'price' => 1100.00,
                    'stock' => 10,
                    'approval_status' => 'approved',
                ],
                [
                    'category' => 'Handwoven Bags',
                    'name' => 'Crossbody Sling Bag',
                    'description' => 'Trendy handwoven crossbody bag with adjustable strap. Features zippered compartment and inner pocket. Size: 25cm x 20cm x 8cm.',
                    'price' => 850.00,
                    'stock' => 15,
                    'approval_status' => 'approved',
                ],
                [
                    'category' => 'Kitchenware',
                    'name' => 'Woven Bread Basket',
                    'description' => 'Charming bread basket with cloth lining. Keeps bread fresh and adds rustic charm to your table. Diameter: 25cm, Height: 10cm.',
                    'price' => 420.00,
                    'stock' => 25,
                    'approval_status' => 'approved',
                ],
                [
                    'category' => 'Home Decor',
                    'name' => 'Woven Wall Art',
                    'description' => 'Contemporary woven wall art featuring geometric patterns. Unique statement piece for modern homes. Size: 60cm x 60cm.',
                    'price' => 1800.00,
                    'stock' => 3,
                    'approval_status' => 'approved',
                ],
            ],
        ];

        $totalProducts = 0;
        $approvedCount = 0;
        $pendingCount = 0;

        foreach ($productsData as $artisanEmail => $products) {
            $artisan = $artisans->firstWhere('email', $artisanEmail);
            
            if (!$artisan) {
                $this->command->warn("Artisan not found: {$artisanEmail}");
                continue;
            }

            foreach ($products as $productData) {
                $category = $categories->firstWhere('name', $productData['category']);
                
                if (!$category) {
                    $this->command->warn("Category not found: {$productData['category']}");
                    continue;
                }

                $product = Product::create([
                    'artisan_id' => $artisan->id,
                    'category_id' => $category->id,
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'stock' => $productData['stock'],
                    'approval_status' => $productData['approval_status'],
                    'is_active' => true,
                ]);

                // Create approval record
                ProductApproval::create([
                    'product_id' => $product->id,
                    'status' => $productData['approval_status'],
                    'reviewed_by' => $productData['approval_status'] === 'approved' ? $admin->id : null,
                    'reviewed_at' => $productData['approval_status'] === 'approved' ? now() : null,
                    'notes' => $productData['approval_status'] === 'approved' 
                        ? 'Product meets quality standards and accurately represents local craftsmanship.' 
                        : null,
                ]);

                // Note: No actual images created (would require image files)
                // In production, you would upload actual product images
                
                $totalProducts++;
                if ($productData['approval_status'] === 'approved') {
                    $approvedCount++;
                } else {
                    $pendingCount++;
                }

                $status = $productData['approval_status'] === 'approved' ? '✓' : '⏳';
                $this->command->info("{$status} Product created: {$product->name} (₱{$product->price}) - {$artisan->name}");
            }
        }

        $this->command->info('');
        $this->command->info("Total products created: {$totalProducts}");
        $this->command->info("Approved: {$approvedCount}");
        $this->command->info("Pending approval: {$pendingCount}");
        $this->command->warn('Note: Product images not seeded (requires actual image files)');
    }
}