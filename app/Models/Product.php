<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products'; // Nom de la table en BDD
    protected $primaryKey = 'id_product'; // Clé primaire personnalisée
    public $timestamps = false; // Désactiver les colonnes created_at et updated_at si non présentes

    protected $fillable = [
        'title',       // Correspond au nom de la colonne
        'description',
        'price',
        'stock',
    ];

    /**
     * Alias pour transformer Product en Article (utilisé par le frontend).
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['name'] = $this->title; // Alias pour le titre
        unset($array['title']); // Supprime "title" pour éviter la confusion
        return $array;
    }

    /**
     * Relation : Un produit appartient à un utilisateur
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id_user');
    }

    /**
     * Relation : Un produit appartient à une catégorie
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
