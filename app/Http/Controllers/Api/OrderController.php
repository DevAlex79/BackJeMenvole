<?php

namespace App\Http\Controllers\Api;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderCompletedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    // -------------------------------------------------------------------------
    // LECTURE
    // -------------------------------------------------------------------------

    /**
     * Liste les commandes accessibles à l'utilisateur connecté.
     *
     * - Admin  → toutes les commandes (paginées, pour le back-office)
     * - Client → uniquement ses propres commandes (tableau plat pour le frontend)
     *
     * Note : on conserve ->get() pour les clients afin de ne pas changer
     * le format de réponse attendu par le frontend Vue.js (tableau JSON direct).
     * La pagination est réservée aux vues admin qui gèrent de grands volumes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();

        if (RoleEnum::isAdmin($user->Roles_id_role)) {
            // Admin : réponse paginée (peut avoir des milliers de commandes)
            $orders = Order::with('user')->paginate(20);
        } else {
            // Client/Vendeur : toutes ses commandes en tableau plat
            $orders = Order::where('users_id_user', $user->id_user)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'Aucune commande trouvée'], 404);
        }

        return response()->json($orders, 200);
    }

    /**
     * Retourne les commandes d'un utilisateur spécifique.
     *
     * Seul l'utilisateur lui-même peut consulter ses commandes.
     * Retourne un tableau plat (->get()) pour rester compatible avec
     * le format attendu par le frontend Vue.js.
     *
     * @param  int  $id  Identifiant de l'utilisateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserOrders($id)
    {
        $user = Auth::user();

        if ($user->id_user != $id) {
            return response()->json(['error' => 'Accès non autorisé.'], 403);
        }

        $orders = Order::where('users_id_user', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'Aucune commande trouvée.'], 404);
        }

        return response()->json($orders, 200);
    }

    /**
     * Retourne toutes les commandes avec les relations user et items.
     * Accessible uniquement aux administrateurs (protégé en route par jwt.auth:3).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllOrders()
    {
        $orders = Order::with('user', 'items')->paginate(20);

        return response()->json($orders, 200);
    }

    /**
     * Retourne les commandes soft-deletées (archivées).
     * Accessible uniquement aux administrateurs (protégé en route par jwt.auth:3).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getArchivedOrders()
    {
        $archivedOrders = Order::onlyTrashed()->with('user')->paginate(20);

        if ($archivedOrders->isEmpty()) {
            return response()->json(['message' => 'Aucune commande archivée trouvée'], 404);
        }

        return response()->json($archivedOrders, 200);
    }

    // -------------------------------------------------------------------------
    // CRÉATION
    // -------------------------------------------------------------------------

    /**
     * Crée une nouvelle commande.
     *
     * Stratégie de gestion du stock :
     *   1. Toutes les vérifications de stock se font dans une transaction DB
     *      avec verrouillage pessimiste (lockForUpdate) pour éviter les
     *      race conditions lors de commandes simultanées.
     *   2. Si le stock est insuffisant pour un produit, toute la transaction
     *      est annulée (rollback automatique).
     *   3. La notification est envoyée hors transaction pour ne pas bloquer le commit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user'          => 'required|exists:users,id_user',
            'cart'             => 'required|array|min:1',
            'cart.*.title'     => 'required|string',
            'cart.*.quantity'  => 'required|integer|min:1',
            'cart.*.unitPrice' => 'required|numeric|min:0',
            'total_price'      => 'required|numeric|min:0',
            'status'           => 'required|string|in:en attente,confirmée,expédiée,livrée',
            'shipmentType'     => 'required|string',
            'shipmentPrice'    => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Log::info('Création de commande en cours', ['id_user' => $request->id_user]);

        try {
            // -----------------------------------------------------------------
            // Transaction avec verrouillage pessimiste pour éviter les race
            // conditions sur le stock (plusieurs commandes simultanées).
            // lockForUpdate() bloque la ligne jusqu'à la fin de la transaction.
            // -----------------------------------------------------------------
            $order = DB::transaction(function () use ($request) {

                foreach ($request->cart as $item) {
                    $product = Product::where('title', $item['title'])
                        ->lockForUpdate()
                        ->first();

                    if (!$product) {
                        throw new \Exception("Le produit '{$item['title']}' n'existe pas.", 404);
                    }

                    if ($product->stock < $item['quantity']) {
                        throw new \Exception(
                            "Stock insuffisant pour '{$item['title']}'. " .
                            "Disponible : {$product->stock}, demandé : {$item['quantity']}.",
                            400
                        );
                    }

                    // Décrémentation atomique garantie par le verrou
                    $product->decrement('stock', $item['quantity']);
                }

                return Order::create([
                    'users_id_user' => $request->id_user,
                    'cart'          => json_encode($request->cart),
                    'total_price'   => $request->total_price,
                    'status'        => $request->status,
                ]);
            });

            Log::info('Commande créée avec succès', ['id_order' => $order->id_order]);

            // Notification isolée dans son propre try/catch :
            // une panne SMTP ne doit PAS annuler la réponse de succès.
            // La commande est déjà commitée en base, on log l'erreur et on continue.
            try {
                $order->user->notify(new OrderCompletedNotification($order));
            } catch (\Exception $notifEx) {
                Log::warning('Notification commande non envoyée (SMTP ?)', [
                    'id_order' => $order->id_order,
                    'error'    => $notifEx->getMessage(),
                ]);
            }

            return response()->json(['message' => 'Commande créée avec succès', 'order' => $order], 201);

        } catch (\Exception $e) {
            // Erreurs métier : stock insuffisant (400), produit introuvable (404), autres (500)
            $statusCode = in_array($e->getCode(), [400, 404]) ? $e->getCode() : 500;

            Log::error('Erreur lors de la création de commande', ['error' => $e->getMessage()]);

            return response()->json(['error' => $e->getMessage()], $statusCode);
        }
    }

    /**
     * Finalise une commande (endpoint legacy, conservé pour compatibilité).
     *
     * Même logique transactionnelle que store() :
     * vérifie puis décrémente le stock de façon atomique avec lockForUpdate.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user'           => 'required|exists:users,id_user',
            'total_price'       => 'required|numeric|min:0',
            'status'            => 'required|string|max:20',
            'cart'              => 'required|array',
            'cart.*.id_product' => 'required|exists:products,id_product',
            'cart.*.quantity'   => 'required|integer|min:1',
        ], [
            'id_user.required'     => "L'identifiant de l'utilisateur est requis.",
            'id_user.exists'       => "L'utilisateur spécifié n'existe pas.",
            'total_price.required' => 'Le prix total est requis.',
            'total_price.numeric'  => 'Le prix total doit être un nombre.',
            'status.required'      => 'Le statut est requis.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        try {
            $order = DB::transaction(function () use ($validated) {

                foreach ($validated['cart'] as $item) {
                    // Verrou pessimiste sur chaque produit
                    $product = Product::where('id_product', $item['id_product'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($product->stock < $item['quantity']) {
                        throw new \Exception(
                            "Stock insuffisant pour '{$product->title}'. " .
                            "Disponible : {$product->stock}, demandé : {$item['quantity']}.",
                            400
                        );
                    }

                    $product->decrement('stock', $item['quantity']);
                }

                return Order::create([
                    'users_id_user' => $validated['id_user'],
                    'total_price'   => $validated['total_price'],
                    'status'        => $validated['status'],
                    'cart'          => json_encode($validated['cart']),
                ]);
            });

            // Notification isolée : une panne SMTP ne doit pas retourner une erreur au client
            try {
                $order->user->notify(new OrderCompletedNotification($order));
            } catch (\Exception $notifEx) {
                Log::warning('Notification completeOrder non envoyée', [
                    'id_order' => $order->id_order,
                    'error'    => $notifEx->getMessage(),
                ]);
            }

            return response()->json(['message' => 'Commande finalisée avec succès', 'order' => $order], 201);

        } catch (\Exception $e) {
            $statusCode = in_array($e->getCode(), [400, 404]) ? $e->getCode() : 500;
            return response()->json(['error' => $e->getMessage()], $statusCode);
        }
    }

    // -------------------------------------------------------------------------
    // MISE À JOUR
    // -------------------------------------------------------------------------

    /**
     * Met à jour le statut ou le prix total d'une commande.
     *
     * Règles d'autorisation :
     *   - Admin          → peut modifier n'importe quelle commande
     *   - Propriétaire   → uniquement si la commande est encore "en attente"
     *   - Autres         → 403 Forbidden
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  Identifiant de la commande
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['error' => 'Commande introuvable'], 404);
        }

        $user = Auth::user();

        // Vérification d'autorisation :
        // - Admin : accès total
        // - Propriétaire : uniquement si la commande est "en attente"
        $isAdmin        = RoleEnum::isAdmin($user->Roles_id_role);
        $isOwnerPending = ($user->id_user === $order->users_id_user)
                          && ($order->status === 'en attente');

        if (!$isAdmin && !$isOwnerPending) {
            return response()->json(['error' => 'Accès refusé. Vous ne pouvez pas modifier cette commande.'], 403);
        }

        $validated = $request->validate([
            'total_price' => 'sometimes|numeric|min:0',
            'status'      => 'sometimes|string|in:en attente,confirmée,expédiée,livrée',
        ]);

        $order->update($validated);

        return response()->json(['message' => 'Commande mise à jour avec succès', 'order' => $order], 200);
    }

    // -------------------------------------------------------------------------
    // SUPPRESSION / ANNULATION
    // -------------------------------------------------------------------------

    /**
     * Annule (soft-delete) une commande et réintègre les quantités au stock.
     *
     * Règles d'autorisation :
     *   - Admin            → peut annuler n'importe quelle commande
     *   - Propriétaire     → uniquement si la commande est "en attente"
     *
     * La réintégration du stock est effectuée dans une transaction pour
     * garantir la cohérence en cas d'erreur (atomicité).
     *
     * @param  int  $id  Identifiant de la commande
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $order = Order::find($id);
        $user  = Auth::user();

        if (!$order) {
            return response()->json(['error' => 'Commande non trouvée'], 404);
        }

        $isAdmin        = RoleEnum::isAdmin($user->Roles_id_role);
        $isOwnerPending = ($user->id_user === $order->users_id_user)
                          && ($order->status === 'en attente');

        if (!$isAdmin && !$isOwnerPending) {
            return response()->json(['error' => 'Vous ne pouvez pas annuler cette commande.'], 403);
        }

        try {
            DB::transaction(function () use ($order) {
                $cartItems = json_decode($order->cart, true) ?? [];

                // Réintégration du stock dans la même transaction avec verrou
                foreach ($cartItems as $item) {
                    // Supporte les deux formats de panier (id ou id_product)
                    $productId = $item['id'] ?? $item['id_product'] ?? null;

                    if ($productId) {
                        $product = Product::lockForUpdate()->find($productId);
                        if ($product) {
                            $product->increment('stock', $item['quantity']);
                        }
                    }
                }

                $order->delete(); // Soft-delete
            });

            Log::info('Commande annulée et stock rétabli', ['id_order' => $order->id_order]);

            return response()->json(['message' => 'Commande annulée avec succès et stock rétabli'], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'annulation de la commande', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erreur lors de l\'annulation de la commande'], 500);
        }
    }
}
