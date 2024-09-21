<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ToyyibPayService
{
    protected $apiUrl;
    protected $secretKey;
    protected $categoryCode;

    public function __construct()
    {
        $this->apiUrl = config('services.toyyibpay.base_url');
        $this->secretKey = config('services.toyyibpay.secret_key');
        $this->categoryCode = config('services.toyyibpay.category_code');

        // Debug statement to verify environment variables
        if (config('app.debug')) {
            Log::info('ToyyibPayService initialized', [
                'secret_key' => $this->secretKey,
                'category_code' => $this->categoryCode,
            ]);
        }
    }

    public function createPaymentLink($order)
    {
        $requestParams = [
            'userSecretKey' => $this->secretKey,
            'categoryCode' => $this->categoryCode,
            'billName' => 'Payment for Order #' . $order->order_number,
            'billDescription' => 'Payment for Order #' . $order->order_number,
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => $order->total_price * 100, // Convert to cents
            'billReturnUrl' => route('payment.return'),
            'billCallbackUrl' => route('payment.callback'),
            'billExternalReferenceNo' => $order->order_number,
            'billTo' => $order->client->name,
            'billEmail' => $order->client->email,
            'billPhone' => $order->client->phone ?? '0000000000', // Provide a default phone number if not available
            'billSplitPayment' => 0,
            'billSplitPaymentArgs' => '',
            'billPaymentChannel' => '0',
            'billContentEmail' => 'Thank you for your payment!',
            'billChargeToCustomer' => 1,
        ];

        // Log the request parameters for debugging
        if (config('app.debug')) {
            Log::info('ToyyibPay API request parameters', ['params' => $requestParams]);
        }

        $response = Http::asForm()->post("{$this->apiUrl}/index.php/api/createBill", $requestParams);

        // Debug statement to verify environment variables
        if (config('app.debug')) {
            Log::info('ToyyibPayService initialized', [
                'api_url' => $this->apiUrl,
                'secret_key' => $this->secretKey,
                'category_code' => $this->categoryCode,
            ]);
        }

        // Log the entire response for debugging
        if (config('app.debug')) {
            Log::info('ToyyibPay API response', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body(),
            ]);
        }

        if ($response->successful()) {
            $data = $response->json();

            // Check if the response contains the expected data
            if (isset($data[0]) && isset($data[0]['BillCode'])) {
                return "{$this->apiUrl}/" . $data[0]['BillCode'];
            } else {
                // Log the error or handle it appropriately
                Log::error('ToyyibPay API response is missing expected data', ['response' => $data]);
                return null;
            }
        } else {
            // Log the error response for debugging
            Log::error('ToyyibPay API request failed', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body(),
            ]);
            return null;
        }
    }
}
