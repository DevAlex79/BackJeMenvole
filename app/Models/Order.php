<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    //protected $fillable = ['user_id', 'amount', 'items'];
    protected $table = 'orders'; // Nom de la table en BDD
    protected $primaryKey = 'id_order'; // Clé primaire personnalisée
    public $timestamps = true; // S'assurer que les colonnes `created_at` et `updated_at` sont gérées

    protected $fillable = [
        'users_id_user', // Clé étrangère vers la table users
        'total_price',
        'status',
    ];

    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'user_id', 'id_user');
    // }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id_user', 'id_user');
    }
}
