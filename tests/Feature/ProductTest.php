<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product()
    {
        // Créer un utilisateur admin
        $admin = User::factory()->create(['Roles_id_role' => 3]);

        // Simuler l'authentification
        $this->actingAs($admin, 'api');

        // Données du produit à créer
        $productData = [
            'title' => 'Test Product',
            'description' => 'Description test',
            'price' => 19.99,
            'stock' => 10,
            'categories_id_category' => 1
        ];

        // Envoyer la requête POST
        $response = $this->postJson('/api/articles', $productData);

        // Vérifier que le produit a bien été ajouté
        $response->assertStatus(201);
        $this->assertDatabaseHas('products', ['title' => 'Test Product']);
    }
}
