<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories'; // Vérifie que c'est bien le nom de ta table
    protected $fillable = ['name']; // Mets les colonnes correctes

    /**
     * Relation : Une catégorie contient plusieurs produits
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
