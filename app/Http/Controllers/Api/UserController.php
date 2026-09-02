<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Crée un nouvel utilisateur (Admin, Vendeur ou Client).
     * Réservé aux administrateurs (UserPolicy::create + route role:3).
     */
    public function createUser(Request $request)
    {
        Gate::authorize('create', User::class);

        $validated = $request->validate([
            'username'      => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users,email',
            'password'      => 'required|string|min:8',
            'Roles_id_role' => 'required|integer|exists:roles,id_role',
        ]);

        $newUser = User::create([
            'username'      => $validated['username'],
            'email'         => $validated['email'],
            'password'      => $validated['password'], // hashé par le cast 'hashed'
            'Roles_id_role' => $validated['Roles_id_role'],
        ]);

        Log::info('Utilisateur créé par un admin', [
            'by'          => Auth::id(),
            'new_user_id' => $newUser->id_user,
            'role'        => $newUser->Roles_id_role,
        ]);

        return response()->json(['message' => 'Utilisateur créé avec succès', 'user' => $newUser], 201);
    }

    /**
     * Liste tous les utilisateurs (paginés). Admins uniquement.
     */
    public function index()
    {
        Gate::authorize('viewAny', User::class);

        return response()->json(User::paginate(20), 200);
    }

    /**
     * Affiche un utilisateur. Le propriétaire ou un admin.
     */
    public function show(string $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['error' => 'Utilisateur introuvable'], 404);
        }

        Gate::authorize('view', $user);

        return response()->json($user, 200);
    }

    /**
     * Met à jour un profil. Le propriétaire ou un admin.
     *
     * Le rôle n'est volontairement pas modifiable ici : une élévation de
     * privilèges doit passer par createUser (admin) ou un endpoint dédié.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['error' => 'Utilisateur introuvable'], 404);
        }

        Gate::authorize('update', $user);

        $validated = $request->validate([
            'username' => 'sometimes|string|max:255',
            'email'    => [
                'sometimes', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id_user, 'id_user'),
            ],
            'password' => 'sometimes|string|min:8',
        ]);

        $user->update($validated); // password hashé par le cast 'hashed'

        return response()->json(['message' => 'Utilisateur mis à jour avec succès', 'user' => $user], 200);
    }

    /**
     * Supprime (soft-delete) un utilisateur. Le propriétaire ou un admin.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['error' => 'Utilisateur introuvable'], 404);
        }

        Gate::authorize('delete', $user);

        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé avec succès'], 200);
    }
}
