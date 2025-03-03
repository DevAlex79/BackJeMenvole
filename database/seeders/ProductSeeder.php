<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les ID des vendeurs et administrateurs
        $adminAndVendors = User::whereIn('Roles_id_role', [2, 3])->pluck('id_user')->toArray();

        // Vérifier s'il y a bien des vendeurs/administrateurs
        if (empty($adminAndVendors)) {
            throw new \Exception("Aucun administrateur ou vendeur trouvé. Assurez-vous d'exécuter le UserSeeder avant !");
        }

        // Liste des produits
        $products = [
            [
                'title' => "Des supers sushis que j'ai mangés dans le sud du Japon",
                'description' => "Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint.",
                'categories_id_category' => 1,
                'image' => 'src/jpg/vinicius-benedit--1GEAA8q3wk-unsplash.jpg',
                'alt' => 'sushis',
                'price' => 10.99,
                'stock' => 10,
                'users_id_user' => $adminAndVendors[array_rand($adminAndVendors)]
            ],
            [
                'title' => "Jardiner pour s'aérer la tête et profiter de la nature",
                'description' => "Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint.",
                'categories_id_category' => 2,
                'image' => 'src/jpg/pelargoniums-for-europe-aHFlP2qKrxc-unsplash.jpg',
                'alt' => 'homme qui jardine',
                'price' => 15.99,
                'stock' => 8,
                'users_id_user' => $adminAndVendors[array_rand($adminAndVendors)]
            ],
            [
                'title' => "Jump dans la vie, ou alors jump dans l'eau",
                'description' => "Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint.",
                'categories_id_category' => 3,
                'image' => 'src/jpg/victor-rodriguez-pWOdBS_l9LQ-unsplash.jpg',
                'alt' => 'homme sautant dans l\'eau',
                'price' => 7.99,
                'stock' => 5,
                'users_id_user' => $adminAndVendors[array_rand($adminAndVendors)]
            ],
            [
                'title' => "Un paysage à couper le souffle en Italie",
                'description' => "Amet minim mollit non deserunt ullamco est sit aliqua dolor do amet sint.",
                'categories_id_category' => 4,
                'image' => 'src/jpg/luca-bravo-O453M2Liufs-unsplash.jpg',
                'alt' => 'barque navigant sur l\'eau',
                'price' => 20.00,
                'stock' => 7,
                'users_id_user' => $adminAndVendors[array_rand($adminAndVendors)]
            ]
        ];

        // Insérer les produits
        DB::table('products')->insert($products);
    }

    
    // public function run(): void
    // {
    //     $categories = Category::all();

    //     // Générer 10 produits de test
    //     foreach (range(1, 10) as $i) {
    //         Product::create([
    //             'name' => 'Produit ' . $i,
    //             'description' => 'Description du produit ' . $i,
    //             'price' => rand(10, 100),
    //             'category_id' => $categories->random()->id,
    //         ]);
    //     }
    // }
}
