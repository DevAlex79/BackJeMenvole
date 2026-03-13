<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $table      = 'products';
    protected $primaryKey = 'id_product';

    /**
     * Les timestamps (created_at / updated_at) sont présents dans la migration.
     * On les active ici pour que Laravel les gère automatiquement.
     * (Valeur par défaut dans Eloquent : true — la ligne est donc informative.)
     */
    public $timestamps = true;

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

    /**
     * La transformation title → name est désormais gérée par ProductResource.
     * Le modèle reste un pur miroir de la base de données, sans logique de présentation.
     */

    /**
     * Relation : un produit appartient à un utilisateur (vendeur ou admin).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id_user', 'id_user');
    }

    /**
     * Relation : un produit appartient à une catégorie.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categories_id_category', 'id');
    }
}
