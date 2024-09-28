<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'pickup_request_id',
        'client_id',
        'agent_id',
        'status',
        'total_price',
        'item_photos',
        'delivery_date',
        'notes',
    ];

    // Define the possible statuses
    const STATUS_NEW_ORDER = 'New Order';
    const STATUS_WAITING_FOR_PAYMENT = 'Waiting For Payment';
    const STATUS_CLEANING = 'Cleaning';
    const STATUS_CLEANING_COMPLETE = 'Cleaning Complete';
    const STATUS_WAITING_FOR_DELIVERY = 'Waiting For Delivery';
    const STATUS_DELIVERED = 'Delivered';
    const STATUS_INVOICE_PAID = 'Invoice Paid';

    // Accessor for the status
    public function getStatusAttribute($value)
    {
        return ucfirst($value);
    }

    // Mutator for the status
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = strtolower($value);
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    public function pickupRequest()
    {
        return $this->belongsTo(PickupRequest::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function generateInvoice()
    {
        $totalPrice = $this->orderItems->sum('price'); // Ensure this is correct

        $invoice = new Invoice([
            'order_id' => $this->id,
            'client_name' => $this->client->name,
            'client_address' => $this->client->address,
            'order_number' => $this->order_number,
            'total_price' => $totalPrice,
            'invoice_number' => Invoice::generateInvoiceNumber(),
        ]);

        $this->invoice()->save($invoice);

        // Update the total_price of the order
        $this->update(['total_price' => $totalPrice]);

        return $invoice;
    }
}
