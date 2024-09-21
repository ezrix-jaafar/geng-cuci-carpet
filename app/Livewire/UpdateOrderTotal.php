<?php

// app/Http/Livewire/UpdateOrderTotal.php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Order;

class UpdateOrderTotal extends Component
{
    public $order;
    public $totalPrice;

    protected $listeners = ['order-items-updated' => 'calculateTotalPrice'];

    public function mount(Order $order)
    {
        $this->order = $order;
        $this->calculateTotalPrice();
    }

    public function calculateTotalPrice()
    {
        $this->totalPrice = $this->order->calculateTotalPrice();
    }

    public function render()
    {
        return view('livewire.update-order-total');
    }
}

