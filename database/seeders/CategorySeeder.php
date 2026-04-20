<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Bamboo Crafts', 'description' => 'Handcrafted bamboo products from trays to utensils.'],
            ['name' => 'Home Decor', 'description' => 'Decorative items for the home.'],
            ['name' => 'Woven Abaca', 'description' => 'Products woven from abaca fiber.'],
            ['name' => 'Handwoven Bags', 'description' => 'Handwoven bags and accessories.'],
            ['name' => 'Native Baskets', 'description' => 'Traditional woven baskets.'],
            ['name' => 'Kitchenware', 'description' => 'Kitchen and dining items.'],
        ];

        foreach ($categories as $data) {
            Category::firstOrCreate(
                ['name' => $data['name']],
                array_merge($data, ['is_active' => true])
            );
        }

        $this->command->info('✓ Categories seeded: ' . count($categories));
    }
}
