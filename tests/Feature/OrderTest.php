<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderCompletedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Création de commandes : stock, calcul du prix côté serveur, sécurité.
 */
class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_une_commande_valide_decremente_le_stock_et_notifie(): void
    {
        Notification::fake();

        $user    = User::factory()->create();
        $product = Product::factory()->stock(10)->create(['price' => 5]);

        $response = $this->postJson('/api/orders', [
            'cart' => [['id_product' => $product->id_product, 'quantity' => 2]],
        ], $this->authHeaders($user));

        $response->assertCreated()->assertJsonPath('order.total_price', '10.00');

        $this->assertDatabaseHas('products', ['id_product' => $product->id_product, 'stock' => 8]);
        $this->assertDatabaseHas('orders', ['users_id_user' => $user->id_user]);
        Notification::assertSentTo($user, OrderCompletedNotification::class);
    }

    public function test_le_prix_envoye_par_le_client_est_ignore(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->stock(10)->create(['price' => 30]);

        $response = $this->postJson('/api/orders', [
            'cart'        => [['id_product' => $product->id_product, 'quantity' => 1]],
            'total_price' => 0,          // tentative de manipulation
            'status'      => 'livrée',   // tentative de forcer le statut
        ], $this->authHeaders($user));

        $response->assertCreated()
            ->assertJsonPath('order.total_price', '30.00')
            ->assertJsonPath('order.status', 'en attente');
    }

    public function test_le_client_de_la_commande_est_celui_du_token(): void
    {
        $user   = User::factory()->create();
        $autre  = User::factory()->create();
        $product = Product::factory()->stock(10)->create();

        $response = $this->postJson('/api/orders', [
            'id_user' => $autre->id_user, // doit être ignoré
            'cart'    => [['id_product' => $product->id_product, 'quantity' => 1]],
        ], $this->authHeaders($user));

        $response->assertCreated();
        $this->assertDatabaseHas('orders', [
            'id_order'      => $response->json('order.id_order'),
            'users_id_user' => $user->id_user,
        ]);
    }

    public function test_stock_insuffisant_refuse_la_commande(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->stock(1)->create();

        $this->postJson('/api/orders', [
            'cart' => [['id_product' => $product->id_product, 'quantity' => 5]],
        ], $this->authHeaders($user))->assertStatus(422);

        $this->assertDatabaseHas('products', ['id_product' => $product->id_product, 'stock' => 1]);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_panier_vide_refuse(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/orders', ['cart' => []], $this->authHeaders($user))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cart']);
    }

    public function test_annulation_retablit_le_stock(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->stock(10)->create();

        $create = $this->postJson('/api/orders', [
            'cart' => [['id_product' => $product->id_product, 'quantity' => 3]],
        ], $this->authHeaders($user))->assertCreated();

        $this->assertDatabaseHas('products', ['id_product' => $product->id_product, 'stock' => 7]);

        $orderId = $create->json('order.id_order');
        $this->deleteJson("/api/orders/{$orderId}", [], $this->authHeaders($user))->assertOk();

        $this->assertDatabaseHas('products', ['id_product' => $product->id_product, 'stock' => 10]);
        $this->assertSoftDeleted('orders', ['id_order' => $orderId]);
    }
}
