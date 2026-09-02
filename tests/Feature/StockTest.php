<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Mise à jour du stock via PUT /api/articles/{id}/stock.
 */
class StockTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_admin_peut_mettre_a_jour_le_stock(): void
    {
        $admin   = User::factory()->admin()->create();
        $product = Product::factory()->stock(10)->create();

        $this->putJson("/api/articles/{$product->id_product}/stock", ['stock' => 20], $this->authHeaders($admin))
            ->assertOk();

        $this->assertDatabaseHas('products', ['id_product' => $product->id_product, 'stock' => 20]);
    }

    public function test_un_vendeur_ne_peut_pas_modifier_le_stock_d_un_autre(): void
    {
        $vendeur = User::factory()->vendeur()->create();
        $product = Product::factory()->create(); // appartient à un autre vendeur

        $this->putJson("/api/articles/{$product->id_product}/stock", ['stock' => 99], $this->authHeaders($vendeur))
            ->assertForbidden();
    }
}
