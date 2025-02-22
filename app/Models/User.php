<?php

namespace App\Models;

//use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Role;
use App\Scopes\ArchivedScope;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, Authorizable, HandlesAuthorization; //AuthenticatableContract;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'users'; // Aligner avec le nom de la table en BDD
    protected $primaryKey = 'id_user'; // Indiquer la clé primaire
    protected $dates = ['deleted_at']; // Ajouté

    protected $fillable = [
        'username', // Aligner avec "username" en BDD
        'password',
        'email',
        'Roles_id_role', // Clé étrangère vers la table roles
        'email_verified_at', // Ajouté
        'remember_token',    // Ajouté
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relation : Un utilisateur possède plusieurs produits
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relation avec le rôle (belongsTo).
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'Roles_id_role', 'id_role');
    }

    // Application du Scope
    protected static function booted()
    {
        static::addGlobalScope(new ArchivedScope);
    }

    // Ajout de la fonctionnalité JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

}
