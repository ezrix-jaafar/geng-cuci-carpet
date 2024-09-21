<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Filament\Resources\PickupRequestResource;

Route::resource('pickup-requests', PickupRequestResource::class);

Route::post('/payment/callback', function () {
    // Handle the callback from ToyyibPay
    $response = request()->all();

    // Log the callback response for debugging
    Log::info('ToyyibPay Callback Response', ['response' => $response]);

    // Log the request headers for debugging
    Log::info('Request Headers', ['headers' => request()->headers->all()]);

    // Check if the payment is successful
    if (isset($response['status_id']) && $response['status_id'] == 1) {
        // Find the order by external reference number
        $order = Order::where('order_number', $response['billcode'])->first();

        if ($order) {
            // Log the order before update
            Log::info('Order Before Update', ['order' => $order->toArray()]);

            // Update the order status to "Invoice Paid"
            $order->update(['status' => 'Invoice Paid']);

            // Log the order after update
            Log::info('Order After Update', ['order' => $order->toArray()]);

            // Log the order update for debugging
            Log::info('Order Status Updated', ['order_number' => $order->order_number, 'status' => 'Invoice Paid']);
        } else {
            // Log the error if the order is not found
            Log::error('Order Not Found', ['billcode' => $response['billcode']]);
        }
    } else {
        // Log the error if the payment is not successful
        Log::error('Payment Not Successful', ['response' => $response]);
    }

    // Return a response to ToyyibPay
    return response()->json(['status' => 'OK']);
})->name('payment.callback');


    Route::get('/payment/return', function () {
        // Handle the return URL from ToyyibPay
        return view('payment.return');
    })->name('payment.return');



// routes/web.php
Route::get('/debug-env', function () {
    return [
        'TOYYIBPAY_SECRET_KEY' => env('TOYYIBPAY_SECRET_KEY'),
        'TOYYIBPAY_CATEGORY_CODE' => env('TOYYIBPAY_CATEGORY_CODE'),
    ];
});
