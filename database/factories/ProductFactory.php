<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'title'                  => fake()->unique()->sentence(3),
            'description'            => fake()->paragraph(),
            'price'                  => fake()->randomFloat(2, 1, 200),
            'stock'                  => fake()->numberBetween(0, 50),
            'categories_id_category' => Category::factory(),
            'users_id_user'          => User::factory()->vendeur(),
            'image'                  => null,
            'alt'                    => null,
        ];
    }

    /** Produit appartenant à un vendeur donné. */
    public function ownedBy(User $user): static
    {
        return $this->state(fn () => ['users_id_user' => $user->id_user]);
    }

    /** Produit avec un stock précis. */
    public function stock(int $quantity): static
    {
        return $this->state(fn () => ['stock' => $quantity]);
    }
}
