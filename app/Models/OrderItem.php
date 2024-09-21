<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'item_description',
        'carpet_size',
        'carpet_type',
        'price',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    protected static function booted()
    {
        static::created(function ($orderItem) {
            $orderItem->order->touch();
        });

        static::updated(function ($orderItem) {
            $orderItem->order->touch();
        });

        static::deleted(function ($orderItem) {
            $orderItem->order->touch();
        });
    }
    public function getTotalPriceAttribute()
    {
        return $this->price * $this->quantity;
    }
}
