{{-- resources/views/customer/orders.blade.php --}}
@extends('layouts.customer')

@section('title', 'Orders')

@section('content')
<div class="row">
    <div class="col">
        <h5 class="mb-0 text-uppercase">Ongoing Orders</h5>
        <hr />
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="orders-table" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Purchase ID</th>
                                <th>Created on</th>
                                <th>Style</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Sub Products</th>
                                <th>Order Quantity</th>
                                <th>Wear Date</th>
                                <th>Total Price</th>
                                <th>ETA</th>
                                <th>Order Status</th>
                                <th>Order Note</th>
                                <th>Edit/Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr @if($order->order_status == "Pending")
                                    style="background-color: rgb(209 198 0); color:black;"
                                @elseif($order->order_status == "Accepted")
                                    style="background-color: #90EE90; color:black;"
                                @elseif($order->order_status == "Placed")
                                    style="background-color: #6495ED; color:black;"
                                @elseif($order->order_status == "Confirmed")
                                    style="background-color: #003200; color:white;"
                                @endif>
                                <form method="POST" action="{{ route('customer.orders.destroy') }}">
                                      @method('DELETE')
                                    @csrf
                                    <td>{{ $order->order_ID }}</td>
                                    <td>{{ $order->purchase_id }}</td>
                                    <td>{{ explode(' ', $order->created_at)[0] }}</td>
                                    <td>{{ strtoupper($order->order_product_style) }}</td>
                                    <td>{{ strtoupper($order->order_product_color) }}</td>
                                    <td>{{ $order->order_product_size }}</td>
                                     <td>{{ collect(json_decode($order->sub_products))->implode(', ') }}</td>
                                    <td>{{ $order->order_quantity }}</td>
                                    <td>{{ $order->order_wear_date }}</td>
                                    <td>{{ $order->order_purchase_price }}</td>
                                    <td>
                                        @if($order->given_by_invntry > 0 || $order->given_by_onway > 0)
                                            {{ date('Y-m-d', strtotime(explode(' ', $order->created_at)[0] . ' + ' . $order->vendor_days_stock . ' days')) }}
                                        @else
                                            {{ date('Y-m-d', strtotime(explode(' ', $order->created_at)[0] . ' + ' . $order->vendor_days . ' days')) }}
                                        @endif
                                    </td>
                                    <td>{{ $order->order_status }}</td>
                                    <td>{{ $order->order_note }}</td>
                                    <td class="text-center">
                                        @if($order->order_status == 'Pending')
                                            <button type="button" hidden class="btn btn-success mb-0 btn-sm edit-button" 
                                                data-order-id="{{ $order->order_ID }}">
                                                Edit
                                            </button>
                                            <input type="hidden" name="orderID" value="{{ $order->order_ID }}">
                                            <button type="submit" class="btn btn-danger mb-0 mr-0 btn-sm">
                                                Delete
                                            </button>
                                        @else
                                            <button  type="button" hidden class="btn btn-success mb-0 btn-sm hidden" disabled>Edit</button>
                                            <button type="button" class="btn btn-danger mb-0 mr-0 btn-sm" disabled>Delete</button>
                                        @endif
                                    </td>
                                </form>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editOrderModal" tabindex="-1" role="dialog" aria-labelledby="editOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editOrderModalLabel">Edit Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editOrderForm">
                    @csrf
                    <input type="hidden" id="edit_order_id" name="order_id">
                    <div class="form-group">
                        <label for="product_quantity">Product Quantity</label>
                        <input type="text" class="form-control" id="product_quantity" name="quantity">
                    </div>
                    <div class="form-group">
                        <label for="purchase_id">Purchase ID</label>
                        <input type="text" class="form-control" id="purchase_id" name="purchase_id">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveOrderChanges">Save changes</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js-scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#orders-table').DataTable({
            aLengthMenu: [
                [10, 25, 50, 100, 200, -1],
                [10, 25, 50, 100, 200, "All"]
            ],
            dom: 'lBfrtip',
            buttons: ['print']
        });

        // Edit button click handler
        $('.edit-button').click(function() {
            var orderId = $(this).data('order-id');
            
            $.ajax({
                url: "{{ route('customer.orders.update', ':id') }}".replace(':id', orderId),
                type: "GET",
                dataType: "json",
                success: function(response) {
                    $('#edit_order_id').val(response.order_ID);
                    $('#product_quantity').val(response.order_quantity);
                    $('#purchase_id').val(response.purchase_id);
                    $('#editOrderModal').modal('show');
                },
                error: function(xhr) {
                    alert('Error: ' + xhr.responseText);
                }
            });
        });

        // Save changes button click handler
        $('#saveOrderChanges').click(function() {
            var orderId = $('#edit_order_id').val();
            
            $.ajax({
                url: "{{ route('customer.orders.update', ':id') }}".replace(':id', orderId),
                type: "PUT",
                data: $('#editOrderForm').serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $('#editOrderModal').modal('hide');
                        location.reload();
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + xhr.responseText);
                }
            });
        });
    });
</script>
@endsection