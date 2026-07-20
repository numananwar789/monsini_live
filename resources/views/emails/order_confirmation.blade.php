{!! nl2br($emailBody) !!}<br>

<table style="text-align:center;" width="600" cellpadding="2" cellspacing="1" border="0" bgcolor="#FFFFFF">
    <thead>
        <tr> 
            <th>Confirmation Id</th>
            <th>Purchase Id</th>
            <th>Style</th>
            <th>Color</th>
            <th>Size</th>
            <th>Quantity</th>
            <th>Wear Date</th>
            <th>Cost</th>                                        
        </tr>
    </thead>
    <tbody>
        @foreach($orders as $order)
            <tr> 
                <td>{{ $order->order_GUID }}</td> 
                <td>{{ $order->purchase_id }}</td> 
                <td>{{ $order->order_product_style }}</td> 
                <td>{{ $order->order_product_color }}</td> 
                <td>{{ $order->order_product_size }}</td> 
                <td>{{ $order->order_quantity }}</td> 
                <td>{{ $order->order_wear_date }}</td> 
                <td>{{ $order->order_purchase_price }}</td>                                         
            </tr>
        @endforeach
    </tbody>
    <tfoot class="text-center">
        <tr>
            <td colspan="4"></td> 
            <th colspan="2"> Total Cost </th>
            <td colspan="2">{{ $totalCost }}</td>
        </tr>
    </tfoot>
</table>