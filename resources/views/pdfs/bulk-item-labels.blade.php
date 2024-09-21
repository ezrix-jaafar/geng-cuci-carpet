<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Item Labels</title>
    <style>
        @page {
            size: A6;
            margin: 0;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .label-container {
            width: 148mm; /* A6 width */
            height: 105mm; /* A6 height */
            padding: 5mm; /* Creates equal margin on all sides */
            box-sizing: border-box;
        }
        .label {
            width: 100%;
            height: 100%;
            border: 1px solid #000;
            box-sizing: border-box;
        }
        .top-box {
            height: 50%;
            border-bottom: 1px solid #000;
            padding: 10px;
        }
        .bottom-box {
            height: 50%;
        }
        .label-item {
            margin-bottom: 5px;
        }
        .label-title {
            font-weight: bold;
        }
    </style>
</head>
<body>
    @foreach($orderItems as $orderItem)
        <div class="label-container">
            <div class="label">
                <div class="top-box">
                    <div class="label-item">
                        <span class="label-title">Order Number:</span> {{ $orderItem->order->order_number }}
                    </div>
                    <div class="label-item">
                        <span class="label-title">Agent Name:</span> {{ $orderItem->order->agent->name }}
                    </div>
                    <div class="label-item">
                        <span class="label-title">Order Date:</span> {{ $orderItem->order->created_at->format('Y-m-d') }}
                    </div>
                    <div class="label-item">
                        <span class="label-title">Label Number:</span> {{ $loop->iteration }}/{{ $orderItems->count() }}
                    </div>
                </div>
                <div class="bottom-box">
                    <!-- Empty box -->
                </div>
            </div>
        </div>
        @if(!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
</body>
</html>