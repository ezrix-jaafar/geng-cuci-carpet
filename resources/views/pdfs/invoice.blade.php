<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .invoice-header img {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .invoice-details {
            margin-bottom: 30px;
        }
        .invoice-details p {
            margin: 0;
            padding: 5px 0;
        }
        .invoice-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .invoice-items th, .invoice-items td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .invoice-items th {
            background-color: #007bff;
            color: #fff;
        }
        .invoice-total {
            text-align: right;
            margin-top: 20px;
        }
        .invoice-total p {
            margin: 0;
            padding: 5px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #777;
        }
        .footer p {
            margin: 0;
            padding: 5px 0;
        }
        .payment-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .payment-link:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="invoice-header">
            <img src="{{ asset('images/logo.png') }}" alt="Company Logo">
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
        <a href="{{ $payment_link }}?status_id=1&billcode={{ $order_number }}" class="payment-link">Make Payment</a>
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Contact us at: <a href="mailto:info@yourcompany.com">info@yourcompany.com</a></p>
        </div>
    </div>
</body>
</html>
