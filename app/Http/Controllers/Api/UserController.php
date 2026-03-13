<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Crée un nouvel utilisateur (Admin, Vendeur ou Client).
     * Accessible uniquement aux administrateurs via la UserPolicy::create().
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUser(Request $request)
    {
        $user = Auth::user();

        if (!Gate::allows('create', User::class)) {
            Log::warning('Accès refusé à la création d\'utilisateur', [
                'user_id' => $user->id_user ?? 'Non authentifié',
            ]);
            return response()->json([
                'error' => 'Accès refusé. Seuls les administrateurs peuvent créer des utilisateurs.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'username'      => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users',
            'password'      => 'required|string|min:8',
            'Roles_id_role' => 'required|integer|exists:roles,id_role',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $newUser = User::create([
            'username'      => $request->username,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'Roles_id_role' => $request->Roles_id_role,
        ]);

        Log::info('Utilisateur créé avec succès', [
            'new_user_id' => $newUser->id_user,
            'role'        => $newUser->Roles_id_role,
        ]);

        return response()->json(['message' => 'Utilisateur créé avec succès', 'user' => $newUser], 201);
    }

    /**
     * Liste tous les utilisateurs (paginés, 20 par page).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $users = User::paginate(20);

            if ($users->isEmpty()) {
                return response()->json(['message' => 'Aucun utilisateur trouvé'], 404);
            }

            return response()->json($users, 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des utilisateurs : ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur interne est survenue'], 500);
        }
    }

    /**
     * Affiche un utilisateur spécifique.
     *
     * @param  int  $id  Identifiant de l'utilisateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Utilisateur introuvable'], 404);
        }

        return response()->json($user, 200);
    }

    /**
     * Met à jour le profil d'un utilisateur.
     * Un utilisateur peut modifier son propre profil ; un admin peut modifier n'importe quel profil.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int                       $id  Identifiant de l'utilisateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Utilisateur introuvable'], 404);
        }

        $validated = $request->validate([
            'username' => 'sometimes|string|max:255',
            'email'    => 'sometimes|string|email|max:255|unique:users,email,' . $id . ',id_user',
            'password' => 'sometimes|string|min:8',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json(['message' => 'Utilisateur mis à jour avec succès', 'user' => $user], 200);
    }

    /**
     * Supprime (soft-delete) un utilisateur.
     * Contrôlé par UserPolicy::delete().
     *
     * @param  int  $id  Identifiant de l'utilisateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Utilisateur introuvable'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé avec succès'], 200);
    }

    /**
     * Méthode stub — non utilisée (la création passe par createUser()).
     */
    public function store()
    {
        // Non utilisé — la création passe par createUser()
    }
}
