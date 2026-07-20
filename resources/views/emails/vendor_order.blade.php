<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Order - {{ $vendorPurchaseId }}</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table th, table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: center;
        }
        h3, h4 {
            margin: 10px 0;
        }
        p {
            line-height: 1.5;
        }
    </style>
</head>
<body>

<h2>New Order - {{ $vendorPurchaseId }}</h2>

<p>{!! nl2br($emailBody) !!}</p>

<h4><strong>Please ship by {{ $shipByDate }}</strong></h4>

@foreach($styleGroups as $style => $colorGroups)
    <h3>Style: {{ strtoupper($style) }}</h3>

    @foreach($colorGroups as $color => $sizes)
        <p><strong>Color:</strong> {{ strtoupper($color) }}</p>

        <table>
            <thead>
                <tr>
                    {{--@for($i = $minSize; $i <= $maxSize; $i += 2)--}}
                    {{--    <th>{{ $i }}</th>--}}
                    {{--@endfor--}}
                    
                    @foreach($sizeHeaders as $size)
                        <th>{{ $size }}</th>
                    @endforeach
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    {{--@for($i = $minSize; $i <= $maxSize; $i += 2)--}}
                        {{--<td>{{ $sizes[$i] ?? '-' }}</td>--}}
                    {{--@endfor--}}
                    @foreach($sizeHeaders as $size)
                        <td>{{ $sizes[$size] ?? '-' }}</td>
                    @endforeach
                    <td>{{ array_sum($sizes) }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach
@endforeach

{{-- <p><strong>Total Price:</strong> ${{ number_format($totalPrice, 2) }}</p> --}}
<p><strong>Vendor Purchase ID:</strong> {{ $vendorPurchaseId }}</p>

<h2> Order Notes</h2>


        <table>
            <thead>
                <tr>
                  <th>OrderID</th>
                  <th>Style</th>
                  <th>Size</th>
                  <th>Color</th>
                  <th>Quantity</th>
                  <th>Sub Products</th>
                  <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                @if(($order->order_note && $order->order_note != "NA") || $order->sub_products)
                <tr>
                    <td> {{  $order->order_ID }}</td>
                    <td> {{ strtoupper($order->order_product_style) }}</td>
                    <td> {{ strtoupper($order->order_product_size) }}</td>
                    <td> {{ strtoupper($order->order_product_color) }}</td>
                    <td> {{ $order->order_quantity}}</td>
                    <td>{{ implode(', ', $order->sub_products ?? []) }}</td>
                    <td> {{  $order->order_note }}</td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>

<p>Thanks,<br>{{ config('app.name') }}</p>

</body>
</html>
