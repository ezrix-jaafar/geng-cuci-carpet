<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .invoice-details {
            margin-bottom: 20px;
        }
        .invoice-items {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-items th, .invoice-items td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .invoice-total {
            text-align: right;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <h2>Invoice</h2>
        <p>Order Number: {{ $order_number }}</p>
    </div>
    <div class="invoice-details">
        <p><strong>Client Name:</strong> {{ $client_name }}</p>
        <p><strong>Client Address:</strong> {{ $client_address }}</p>
    </div>
    <table class="invoice-items">
        <thead>
            <tr>
                <th>Item Description</th>
                <th>Carpet Size</th>
                <th>Carpet Type</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order_items as $item)
            <tr>
                <td>{{ $item->item_description }}</td>
                <td>{{ $item->carpet_size }}</td>
                <td>{{ $item->carpet_type }}</td>
                <td>{{ $item->price }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->total_price }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="invoice-total">
        <p><strong>Total Price:</strong> {{ $total_price }}</p>
    </div>


    <h2>Payment</h2>
    <p>Please make your payment using the following link:</p>
    {{-- <a href="{{ $payment_link }}">Make Payment</a> --}}

    <a href="{{ $payment_link }}?status_id=1&billcode={{ $order_number }}">Make Payment</a>


</body>
</html>
