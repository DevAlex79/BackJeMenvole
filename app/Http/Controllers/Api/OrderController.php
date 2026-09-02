<?php

namespace App\Http\Controllers\Api;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Notifications\OrderCompletedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /** Statuts de commande autorisés. */
    private const STATUSES = ['en attente', 'confirmée', 'expédiée', 'livrée'];

    // -------------------------------------------------------------------------
    // LECTURE
    // -------------------------------------------------------------------------

    /**
     * Liste les commandes accessibles à l'utilisateur connecté.
     *
     * - Admin  → toutes les commandes, paginées
     * - Autres → uniquement ses propres commandes
     *
     * Renvoie toujours 200 (tableau/paginator, éventuellement vide).
     */
    public function index()
    {
        $user = Auth::user();

        if (RoleEnum::isAdmin($user->Roles_id_role)) {
            return response()->json(Order::with('user')->latest()->paginate(20));
        }

        $orders = Order::where('users_id_user', $user->id_user)
            ->with('user')
            ->latest()
            ->get();

        return response()->json($orders);
    }

    /**
     * Commandes d'un utilisateur donné. Le propriétaire ou un admin.
     */
    public function getUserOrders($id)
    {
        $user = Auth::user();

        if ((int) $user->id_user !== (int) $id && ! RoleEnum::isAdmin($user->Roles_id_role)) {
            return response()->json(['error' => 'Accès non autorisé.'], 403);
        }

        $orders = Order::where('users_id_user', $id)->latest()->get();

        return response()->json($orders);
    }

    /**
     * Toutes les commandes avec relations. Admins uniquement (route role:3).
     */
    public function getAllOrders()
    {
        return response()->json(Order::with('user')->latest()->paginate(20));
    }

    /**
     * Commandes archivées (soft-deletées). Admins uniquement (route role:3).
     */
    public function getArchivedOrders()
    {
        return response()->json(Order::onlyTrashed()->with('user')->latest('deleted_at')->paginate(20));
    }

    // -------------------------------------------------------------------------
    // CRÉATION
    // -------------------------------------------------------------------------

    /**
     * Crée une commande pour l'utilisateur connecté.
     *
     * Points de sécurité :
     *   - le client de la commande est TOUJOURS l'utilisateur du token,
     *     jamais un id envoyé dans le corps de la requête ;
     *   - les prix unitaires et le total sont recalculés côté serveur à
     *     partir de la table products — le client ne fournit que les
     *     id_product et les quantités ;
     *   - le statut initial est imposé à « en attente ».
     *
     * Le stock est vérifié puis décrémenté dans une transaction avec
     * verrou pessimiste (lockForUpdate) — efficace uniquement en InnoDB.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cart'              => 'required|array|min:1',
            'cart.*.id_product' => 'required|integer|exists:products,id_product',
            'cart.*.quantity'   => 'required|integer|min:1',
            'shipment_type'     => 'nullable|string|max:255',
            'shipment_price'    => 'nullable|numeric|min:0',
        ]);

        $userId        = Auth::user()->id_user;
        $shipmentType  = $validated['shipment_type'] ?? null;
        $shipmentPrice = round((float) ($validated['shipment_price'] ?? 0), 2);

        try {
            $order = DB::transaction(function () use ($validated, $userId, $shipmentType, $shipmentPrice) {
                $lines      = [];
                $itemsTotal = 0.0;

                foreach ($validated['cart'] as $item) {
                    $product = Product::where('id_product', $item['id_product'])
                        ->lockForUpdate()
                        ->first();

                    if (! $product) {
                        throw new \RuntimeException("Produit {$item['id_product']} introuvable.", 404);
                    }

                    if ($product->stock < $item['quantity']) {
                        throw new \RuntimeException(
                            "Stock insuffisant pour « {$product->title} ». " .
                            "Disponible : {$product->stock}, demandé : {$item['quantity']}.",
                            422
                        );
                    }

                    $product->decrement('stock', $item['quantity']);

                    $unitPrice   = (float) $product->price;
                    $itemsTotal += $unitPrice * $item['quantity'];

                    $lines[] = [
                        'id_product' => $product->id_product,
                        'title'      => $product->title,
                        'quantity'   => $item['quantity'],
                        'unit_price' => round($unitPrice, 2),
                    ];
                }

                return Order::create([
                    'users_id_user'  => $userId,
                    'cart'           => $lines,
                    'total_price'    => round($itemsTotal + $shipmentPrice, 2),
                    'status'         => 'en attente',
                    'shipment_type'  => $shipmentType,
                    'shipment_price' => $shipmentPrice,
                ]);
            });
        } catch (\RuntimeException $e) {
            $status = in_array($e->getCode(), [404, 422], true) ? $e->getCode() : 500;
            Log::warning('Commande refusée', ['user' => $userId, 'error' => $e->getMessage()]);

            return response()->json(['error' => $e->getMessage()], $status);
        }

        Log::info('Commande créée', ['id_order' => $order->id_order, 'user' => $userId]);

        // Notification isolée : une panne SMTP ne doit pas invalider la commande.
        try {
            $order->user->notify(new OrderCompletedNotification($order));
        } catch (\Throwable $notifEx) {
            Log::warning('Notification commande non envoyée', [
                'id_order' => $order->id_order,
                'error'    => $notifEx->getMessage(),
            ]);
        }

        return response()->json(['message' => 'Commande créée avec succès', 'order' => $order], 201);
    }

    // -------------------------------------------------------------------------
    // MISE À JOUR
    // -------------------------------------------------------------------------

    /**
     * Met à jour une commande.
     *
     *   - Admin        → peut changer le statut et le montant
     *   - Propriétaire → uniquement tant que la commande est « en attente »,
     *                    et sans toucher au statut ni au prix (il annule via
     *                    DELETE s'il veut renoncer).
     */
    public function update(Request $request, $id)
    {
        $order = Order::find($id);

        if (! $order) {
            return response()->json(['error' => 'Commande introuvable'], 404);
        }

        $user    = Auth::user();
        $isAdmin = RoleEnum::isAdmin($user->Roles_id_role);
        $isOwner = (int) $user->id_user === (int) $order->users_id_user;

        if (! $isAdmin && ! ($isOwner && $order->status === 'en attente')) {
            return response()->json(['error' => 'Accès refusé. Vous ne pouvez pas modifier cette commande.'], 403);
        }

        if ($isAdmin) {
            $validated = $request->validate([
                'total_price' => 'sometimes|numeric|min:0',
                'status'      => ['sometimes', 'string', 'in:' . implode(',', self::STATUSES)],
            ]);
        } else {
            // Le propriétaire ne peut pas modifier statut/prix.
            $validated = $request->validate([
                'shipment_type' => 'sometimes|nullable|string|max:255',
            ]);
        }

        $order->update($validated);

        return response()->json(['message' => 'Commande mise à jour avec succès', 'order' => $order], 200);
    }

    // -------------------------------------------------------------------------
    // ANNULATION (soft-delete + réintégration du stock)
    // -------------------------------------------------------------------------

    /**
     *   - Admin        → peut annuler n'importe quelle commande
     *   - Propriétaire → uniquement tant qu'elle est « en attente »
     */
    public function destroy($id)
    {
        $order = Order::find($id);

        if (! $order) {
            return response()->json(['error' => 'Commande non trouvée'], 404);
        }

        $user    = Auth::user();
        $isAdmin = RoleEnum::isAdmin($user->Roles_id_role);
        $isOwner = (int) $user->id_user === (int) $order->users_id_user;

        if (! $isAdmin && ! ($isOwner && $order->status === 'en attente')) {
            return response()->json(['error' => 'Vous ne pouvez pas annuler cette commande.'], 403);
        }

        try {
            DB::transaction(function () use ($order) {
                foreach ($order->cart ?? [] as $item) {
                    $productId = $item['id_product'] ?? $item['id'] ?? null;
                    $quantity  = $item['quantity'] ?? 0;

                    if ($productId && $quantity > 0) {
                        $product = Product::lockForUpdate()->find($productId);
                        $product?->increment('stock', $quantity);
                    }
                }

                $order->delete();
            });
        } catch (\Throwable $e) {
            Log::error('Erreur lors de l\'annulation de la commande', [
                'id_order' => $order->id_order,
                'error'    => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Erreur lors de l\'annulation de la commande'], 500);
        }

        Log::info('Commande annulée, stock rétabli', ['id_order' => $order->id_order]);

        return response()->json(['message' => 'Commande annulée avec succès et stock rétabli'], 200);
    }
}
