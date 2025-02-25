<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';
    protected $primaryKey = 'id_order_item';
    public $incrementing = true;
    protected $keyType = 'bigint'; 

    protected $fillable = [
        'Orders_id_order',
        'Products_id_product',
        'quantity',
        'price'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'Orders_id_order', 'id_order');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'Products_id_product', 'id_product');
    }
}
