<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OrderCompletedNotification;

class OrderTest extends TestCase
{
    use RefreshDatabase; // Assure que la base de données est remise à zéro après chaque test

    /**
     * T3-001 - Validation du panier (Commande avec produits en stock)
     */
    public function test_validation_du_panier_avec_produits_en_stock()
    {
        Notification::fake(); // Empêche l'envoi réel d'email lors du test

        // Création d'un utilisateur
        $user = User::factory()->create();

        // Création d'un produit en stock
        $product = Product::factory()->create([
            'stock' => 10,
        ]);

        // Simuler une commande valide
        $response = $this->actingAs($user)->postJson('/api/orders', [
            'id_user' => $user->id_user,
            'cart' => [
                ['id_product' => $product->id_product, 'quantity' => 2]
            ],
            'total_price' => $product->price * 2,
            'status' => 'en attente'
        ]);

        $response->assertStatus(201) // Vérifie que la commande est bien créée
                ->assertJson(['message' => 'Commande créée avec succès']);

        // Vérifier qu'une notification a bien été envoyée
        Notification::assertSentTo($user, OrderCompletedNotification::class);
    }

    /**
     * T3-002 - Validation d'un panier vide
     */
    public function test_validation_d_un_panier_vide()
    {
        // Création d'un utilisateur
        $user = User::factory()->create();

        // Simuler une commande vide
        $response = $this->actingAs($user)->postJson('/api/orders', [
            'id_user' => $user->id_user,
            'cart' => [], // Panier vide
            'total_price' => 0,
            'status' => 'en attente'
        ]);

        $response->assertStatus(422) // Vérifie qu'il y a une erreur de validation
                ->assertJsonValidationErrors(['cart']);
    }

    /**
     * T3-003 - Vérification du stock après une commande
     */
    public function test_verification_du_stock_apres_une_commande()
    {
        // Création d'un utilisateur
        $user = User::factory()->create();

        // Création d'un produit avec un stock initial
        $product = Product::factory()->create([
            'stock' => 10,
        ]);

        // Simuler une commande qui utilise du stock
        $this->actingAs($user)->postJson('/api/orders', [
            'id_user' => $user->id_user,
            'cart' => [
                ['id_product' => $product->id_product, 'quantity' => 3]
            ],
            'total_price' => $product->price * 3,
            'status' => 'en attente'
        ]);

        // Vérifier que le stock a bien été décrémenté
        $this->assertDatabaseHas('products', [
            'id_product' => $product->id_product,
            'stock' => 7 // Stock initial (10) - Quantité commandée (3)
        ]);
    }
}
