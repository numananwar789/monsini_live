<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Confirmation</title>
</head>
<body>
    <h2>Order Confirmation</h2>

    {!! nl2br($emailBody) !!}

    <br><br>

    <table border="1" cellpadding="8" cellspacing="0" width="100%" style="border-collapse: collapse; text-align: center;">
        <thead style="background-color: #f2f2f2;">
            <tr>
                <th>Confirmation ID</th>
                <th>Purchase ID</th>
                <th>Style</th>
                <th>Color</th>
                <th>Size</th>
                <th>Quantity</th>
                <th>Wear Date</th>
                <th>Cost</th>
                <th>Vendor Message</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->order_GUID }}</td>
                <td>{{ $order->purchase_id }}</td>
                <td>{{ strtoupper($order->order_product_style) }}</td>
                <td>{{ strtoupper($order->order_product_color) }}</td>
                <td>{{ $order->order_product_size }}</td>
                <td>{{ $order->order_quantity }}</td>
                <td>{{ $order->order_wear_date }}</td>
                <td>${{ number_format($order->order_purchase_price, 2) }}</td>
                <td>{{ $vendorMessages[$order->order_vendor_ID] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6"></td>
                <td colspan="2"><strong>Total Cost</strong></td>
                <td><strong>${{ number_format($totalCost, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <br><br>
    <p>Thanks,<br>{{ config('app.name') }}</p>
</body>
</html>
