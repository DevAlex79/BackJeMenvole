<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Catalogue produits : lecture publique et règles d'écriture.
 */
class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_catalogue_est_public(): void
    {
        Product::factory()->count(3)->create();

        $this->getJson('/api/articles')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_un_admin_peut_creer_un_produit(): void
    {
        $admin    = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->postJson('/api/articles', [
            'title'                  => 'Produit test',
            'description'             => 'Description',
            'price'                  => 19.99,
            'stock'                  => 10,
            'categories_id_category' => $category->id,
        ], $this->authHeaders($admin));

        $response->assertCreated();
        $this->assertDatabaseHas('products', [
            'title'         => 'Produit test',
            'users_id_user' => $admin->id_user,
        ]);
    }

    public function test_un_client_ne_peut_pas_creer_de_produit(): void
    {
        $client   = User::factory()->create(); // rôle Client par défaut
        $category = Category::factory()->create();

        $this->postJson('/api/articles', [
            'title'                  => 'Interdit',
            'price'                  => 10,
            'stock'                  => 1,
            'categories_id_category' => $category->id,
        ], $this->authHeaders($client))->assertForbidden();
    }

    public function test_un_vendeur_ne_voit_que_ses_produits_en_back_office(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        Product::factory()->count(2)->ownedBy($vendeur)->create();
        Product::factory()->count(3)->create(); // ceux d'un autre vendeur

        $response = $this->getJson('/api/articles', $this->authHeaders($vendeur))->assertOk();

        $this->assertCount(2, $response->json('data'));
    }
}
