<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
//use Tymon\JWTAuth\Facades\JWTAuth;



class UserController extends Controller
{
    /**
     * Créer un nouvel utilisateur (Admin ou Vendeur) - Accessible uniquement aux Admins.
     */
    public function createUser(Request $request)
    {
        $user = Auth::user();

        if (!$user || !Gate::allows('create', User::class)) {
            Log::warning("Accès refusé à la création d'utilisateur", ['user_id' => $user->id_user ?? 'Non authentifié']);
            return response()->json(['error' => 'Accès refusé. Seuls les administrateurs peuvent créer des utilisateurs.'], 403);
        }

        // Validation des données
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            //'Roles_id_role' => 'required|in:Administrateur,Vendeur',
            'Roles_id_role' => 'required|integer|exists:roles,id_role',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        //$role = Role::where('role_name', $request->role)->first();
        $role = Role::find($request->Roles_id_role);
        if (!$role) {
            return response()->json(['error' => 'Rôle invalide'], 422);
        }

        // Création de l'utilisateur
        $newUser = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'Roles_id_role' => $request->Roles_id_role,
        ]);

        Log::info("Utilisateur créé avec succès", ['new_user_id' => $newUser->id_user, 'role' => $newUser->Roles_id_role]);

        return response()->json(['message' => 'Utilisateur créé avec succès', 'user' => $newUser], 201);

    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = User::all();
    
            if ($users->isEmpty()) {
                return response()->json(['message' => 'Aucun utilisateur trouvé'], 404);
            }
    
            return response()->json($users, 200);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération des utilisateurs : " . $e->getMessage());
            return response()->json(['error' => 'Une erreur interne est survenue'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Utilisateur introuvable'], 404);
        }

        $validated = $request->validate([
            'username' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id . ',id_user',
            'password' => 'sometimes|string|min:8'
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $user->update($validated);

        return response()->json(['message' => 'Utilisateur mis à jour avec succès', 'user' => $user], 200);
    }

    /**
     * Remove the specified resource from storage.
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
}
