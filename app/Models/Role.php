<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles'; // Aligner avec le nom de la table en BDD
    protected $primaryKey = 'id_role'; // Indiquer la clé primaire

    protected $fillable = [
        'role_name',
    ];

    /**
     * Relation avec les utilisateurs (hasMany).
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'Roles_id_role', 'id_role');
    } 
}
