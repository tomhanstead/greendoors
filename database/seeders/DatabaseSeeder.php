<?php

namespace Database\Seeders;

use App\Models\Category;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ✅ Predefined categories & their products
        $categories = [
            'Electronics' => ['iPhone 16 Pro', 'Samsung Galaxy S25'],
            'Clothing' => ['Nike Running Shoes', 'Adidas Hoodie'],
        ];

        // ✅ Loop through each category and create it with products
        foreach ($categories as $categoryName => $products) {
            Category::factory()
                ->state(['name' => $categoryName]) // Override factory-generated name
                ->hasProducts(2, $products) // Use a custom factory relationship method
                ->create();
        }
    }
}
