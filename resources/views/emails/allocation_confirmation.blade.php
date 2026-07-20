@component('mail::message')
# Order Allocation Confirmation

{!! nl2br($emailBody) !!}

@component('mail::table')
| Confirmation ID | Purchase ID | Style | Color | Size | Quantity | Wear Date | Cost | Vendor Message |
|-----------------|-------------|-------|-------|------|----------|-----------|------|----------------|
@foreach($orders as $order)
| {{ $order->order_GUID }} | {{ $order->purchase_id }} | {{ strtoupper($order->order_product_style) }} | {{ strtoupper($order->order_product_color) }} | {{ $order->order_product_size }} | {{ $order->order_quantity }} | {{ $order->order_wear_date }} | ${{ number_format($order->order_purchase_price, 2) }} | {{ $vendorMessages[$order->order_vendor_ID] }} |
@endforeach

| | | | | | **Total Cost** | | **${{ number_format($totalCost, 2) }}** | |
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent