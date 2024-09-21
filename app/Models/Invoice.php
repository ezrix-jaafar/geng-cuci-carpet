<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'order_id',
        'client_name',
        'client_address',
        'order_number',
        'total_price',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }


    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public static function generateInvoiceNumber()
    {
        $latestInvoice = self::latest('id')->first();
        $nextId = $latestInvoice ? $latestInvoice->id + 1 : 1;
        return 'INV-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    protected static function booted()
    {
        static::deleting(function ($invoice) {
            // Get the associated order
            $order = $invoice->order;

            if ($order) {
                // Set total_price to null or 0 when the invoice is deleted
                $order->update(['total_price' => null]); // Or '0' if you prefer
            }
        });
    }



}
