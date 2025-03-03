<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockTest extends TestCase
{
    use RefreshDatabase;
    public function test_admin_can_update_stock()
    {
        $admin = User::factory()->create(['Roles_id_role' => 3]);
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($admin, 'api');

        $response = $this->putJson("/api/articles/{$product->id}/stock", [
            'stock' => 20
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 20]);
    }
}

