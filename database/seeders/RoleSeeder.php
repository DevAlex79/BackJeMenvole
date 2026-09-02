<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Crée les trois rôles applicatifs dans l'ordre attendu par RoleEnum
     * (1 = Client, 2 = Vendeur, 3 = Administrateur).
     */
    public function run(): void
    {
        foreach (RoleEnum::cases() as $role) {
            Role::firstOrCreate(
                ['id_role' => $role->value],
                ['role_name' => $role->label()]
            );
        }
    }
}
