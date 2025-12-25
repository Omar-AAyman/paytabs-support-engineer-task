<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Laptop', 'price' => 15000.00, 'stock' => 10],
            ['name' => 'Smartphone', 'price' => 8000.00, 'stock' => 20],
            ['name' => 'Headphones', 'price' => 1500.00, 'stock' => 50],
            ['name' => 'Smart Watch', 'price' => 3000.00, 'stock' => 30],
            ['name' => 'Keyboard', 'price' => 800.00, 'stock' => 40],
            ['name' => 'Limited Edition Mouse', 'price' => 2500.00, 'stock' => 2],
            ['name' => 'Old Graphics Card', 'price' => 5000.00, 'stock' => 0],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
