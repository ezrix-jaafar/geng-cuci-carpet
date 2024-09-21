<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'client_id',
        'agent_id',
        'estimated_item_qty',
        'final_item_qty',
        'status',
        'pickup_date',
        'tagging_labels',
        'notes',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pickupRequest) {
            $latestRequest = static::latest('id')->first();
            $nextId = $latestRequest ? $latestRequest->id + 1 : 1;
            $pickupRequest->request_number = 'PR-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    public function order()
    {
        return $this->hasOne(Order::class);
    }
}
