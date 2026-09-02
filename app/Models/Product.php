<?php

namespace App\Models;

use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(ProductObserver::class)]
class Product extends Model
{
    use HasFactory;

    protected $table      = 'products';
    protected $primaryKey = 'id_product';

    protected $fillable = [
        'title',
        'description',
        'price',
        'stock',
        'categories_id_category',
        'users_id_user',
        'image',
        'alt',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    /**
     * Vendeur/admin propriétaire du produit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id_user', 'id_user');
    }

    /**
     * Catégorie du produit.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categories_id_category', 'id');
    }
}
