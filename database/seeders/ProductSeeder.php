<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();

        // Générer 10 produits de test
        foreach (range(1, 10) as $i) {
            Product::create([
                'name' => 'Produit ' . $i,
                'description' => 'Description du produit ' . $i,
                'price' => rand(10, 100),
                'category_id' => $categories->random()->id,
            ]);
        }
    }
}
