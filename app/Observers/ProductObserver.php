<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    /** Seuil d'alerte de stock bas. */
    private const LOW_STOCK_THRESHOLD = 10;

    /**
     * Journalise une alerte quand le stock passe sous le seuil.
     */
    public function updated(Product $product): void
    {
        if ($product->wasChanged('stock') && $product->stock < self::LOW_STOCK_THRESHOLD) {
            Log::warning('Stock bas', [
                'id_product' => $product->id_product,
                'title'      => $product->title,
                'stock'      => $product->stock,
            ]);
        }
    }
}
