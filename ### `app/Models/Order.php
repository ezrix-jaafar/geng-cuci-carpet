<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Invoice;

class Order extends Model
{
    // Other model properties and methods

    public function generateInvoice()
    {
        // Calculate the total price
        $totalPrice = $this->orderItems->sum('price');

        // Update the total price in the order
        $this->update(['total_price' => $totalPrice]);

        // Create the invoice
        $invoice = new Invoice();
        $invoice->order_id = $this->id;
        $invoice->invoice_number = 'INV-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
        $invoice->client_name = $this->client->name;
        $invoice->client_address = $this->client->address;
        $invoice->order_number = $this->order_number;
        $invoice->total_price = $totalPrice;
        $invoice->save();

        // Associate the invoice with the order
        $this->invoice()->save($invoice);

        return $invoice;
    }
}
