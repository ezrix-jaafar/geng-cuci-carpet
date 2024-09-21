<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Label</title>
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
    <div class="label">
        <div class="top-box">
            <div class="label-item">
                <span class="label-title">Order Number:</span> {{ $order_number }}
            </div>
            <div class="label-item">
                <span class="label-title">Agent Name:</span> {{ $agent_name }}
            </div>
            <div class="label-item">
                <span class="label-title">Order Date:</span> {{ $order_date }}
            </div>
            <div class="label-item">
                <span class="label-title">Label Number:</span> {{ $label_number }}
            </div>
        </div>
        <div class="bottom-box">
            <!-- Empty box -->
        </div>
    </div>
</body>
</html>