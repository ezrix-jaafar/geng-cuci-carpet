<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Order;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class OrderTotal extends Component implements HasForms
{
    use InteractsWithForms;

    public $orderId;
    public $total = 0;

    protected $listeners = ['orderItemsUpdated' => 'updateTotal'];

    public function mount($orderId = null)
    {
        $this->orderId = $orderId;
        $this->updateTotal();
    }

    public function updateTotal()
    {
        if ($this->orderId) {
            $order = Order::find($this->orderId);
            $this->total = $order->orderItems->sum('price');
        }
    }

    public function render()
    {
        return view('livewire.order-total');
    }
}