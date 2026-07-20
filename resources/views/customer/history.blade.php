@extends('layouts.customer')

@section('title', 'Order History')

@section('content')
<div class="row">
    <div class="col">
        <h5 class="mb-0 text-uppercase">Ongoing Orders</h5>
        <hr />
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="history-table" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Purchase ID</th>
                                <th>Created on</th>
                                <th>Wear Date</th>
                                <th>Style</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Sub Products</th>
                                <th>Order Quantity</th>
                                <th>Total Price</th>
                                <th>Order Status</th>
                                <th>Order Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr @if($order->order_status == "Pending")
                                    style="background-color: rgb(209 198 0); color:black;"
                                @elseif($order->order_status == "Allocated")
                                    style="background-color: #90EE90; color:black;"
                                @elseif($order->order_status == "Placed")
                                    style="background-color: #6495ED; color:black;"
                                @elseif($order->order_status == "Confirmed")
                                    style="background-color: #003200; color:white;"
                                @endif>
                                <td>{{ $order->order_ID }}</td>
                                <td>{{ $order->purchase_id }}</td>
                                <td>{{ explode(' ', $order->created_at)[0] }}</td>
                                <td>{{ $order->order_wear_date }}</td>
                                <td>{{ strtoupper($order->order_product_style) }}</td>
                                <td>{{ strtoupper($order->order_product_color) }}</td>
                                <td>{{ $order->order_product_size }}</td>
                           <td>
    {{ is_array($order->sub_products) 
        ? implode(', ', $order->sub_products) 
        : (is_string($order->sub_products) 
            ? $order->sub_products 
            : '-') }}
</td>

                                <td>{{ $order->order_quantity }}</td>
                                <td>{{ $order->order_purchase_price }}</td>
                                <td>{{ $order->order_status }}</td>
                                <td>{{ $order->order_note }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js-scripts')
<script>
    $(document).ready(function() {
        $('#history-table').DataTable({
            aLengthMenu: [
                [10, 25, 50, 100, 200, -1],
                [10, 25, 50, 100, 200, "All"]
            ],
            dom: 'lBfrtip',
            buttons: ['print']
        });
    });
</script>
@endsection