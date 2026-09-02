<?php

namespace Database\Factories;

use App\Enums\RoleEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /** Mot de passe partagé (haché une seule fois) pour accélérer les tests. */
    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'username'          => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
            'Roles_id_role'     => $this->roleId(RoleEnum::Client),
        ];
    }

    /** Compte administrateur. */
    public function admin(): static
    {
        return $this->state(fn () => ['Roles_id_role' => $this->roleId(RoleEnum::Administrateur)]);
    }

    /** Compte vendeur. */
    public function vendeur(): static
    {
        return $this->state(fn () => ['Roles_id_role' => $this->roleId(RoleEnum::Vendeur)]);
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    /**
     * Garantit l'existence de la ligne `roles` correspondante (clé étrangère).
     */
    private function roleId(RoleEnum $role): int
    {
        return Role::firstOrCreate(
            ['id_role' => $role->value],
            ['role_name' => $role->label()]
        )->id_role;
    }
}
