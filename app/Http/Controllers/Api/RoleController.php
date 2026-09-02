<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Gestion des rôles applicatifs.
 *
 * Lecture (index / show) : publique — utilisée par le formulaire
 * d'inscription pour afficher les libellés.
 * Écriture (store / update / destroy) : administrateurs uniquement
 * (middleware 'role:3' dans routes/api.php).
 */
class RoleController extends Controller
{
    /**
     * Liste tous les rôles.
     */
    public function index()
    {
        return response()->json(Role::orderBy('id_role')->get(), 200);
    }

    /**
     * Affiche un rôle.
     *
     * @param  string  $id  Identifiant du rôle (id_role)
     */
    public function show(string $id)
    {
        $role = Role::find($id);

        if (! $role) {
            return response()->json(['message' => 'Rôle non trouvé'], 404);
        }

        return response()->json($role, 200);
    }

    /**
     * Crée un rôle.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_name' => 'required|string|max:255|unique:roles,role_name',
        ]);

        return response()->json(Role::create($validated), 201);
    }

    /**
     * Renomme un rôle.
     *
     * @param  string  $id  Identifiant du rôle (id_role)
     */
    public function update(Request $request, string $id)
    {
        $role = Role::find($id);

        if (! $role) {
            return response()->json(['message' => 'Rôle non trouvé'], 404);
        }

        $validated = $request->validate([
            'role_name' => [
                'required', 'string', 'max:255',
                Rule::unique('roles', 'role_name')->ignore($role->id_role, 'id_role'),
            ],
        ]);

        $role->update($validated);

        return response()->json($role, 200);
    }

    /**
     * Supprime un rôle.
     *
     * @param  string  $id  Identifiant du rôle (id_role)
     */
    public function destroy(string $id)
    {
        $role = Role::find($id);

        if (! $role) {
            return response()->json(['message' => 'Rôle non trouvé'], 404);
        }

        $role->delete();

        return response()->json(['message' => 'Rôle supprimé avec succès'], 200);
    }
}
