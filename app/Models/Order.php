<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';
    protected $primaryKey = 'id_order';

    protected $fillable = [
        'users_id_user',
        'cart',
        'total_price',
        'status',
        'shipment_type',
        'shipment_price',
    ];

    protected function casts(): array
    {
        return [
            'cart'           => 'array',
            'total_price'    => 'decimal:2',
            'shipment_price' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id_user', 'id_user');
    }
}
