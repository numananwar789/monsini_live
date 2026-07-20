@extends('layouts.app')

@section('page-css')
    <link href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/datetime/1.1.2/css/dataTables.dateTime.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" rel="stylesheet" />
    <style>
        div#example_filter {
            float: right;
        }

        div#example_length {
            float: left;
        }
    </style>
@endsection

@section('content')
    <!-- [ Main Content ] start -->
    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">
                            <!-- [ Main Content ] start -->
                            <div class="row">
                                <!-- [ daily sales section ] start -->
                                <div class="col">
                                    <h5 class="mb-0 text-uppercase">All Inventory</h5>
                                    
                                                                        @if (session('success'))
                                        <div class="alert alert-success">
                                            {{ session('success') }}
                                        </div>
                                    @endif

                                    @if (session('error'))
                                        <div class="alert alert-danger">
                                            {{ session('error') }}
                                        </div>
                                    @endif



                                    @if (session('errors'))
                                        <div class="alert alert-danger">
                                            <strong>These orders failed:</strong>
                                            <ul>
                                                @foreach (session('errors') as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <hr />
                                    <div class="card">
                                        <div class="card-header" style="display: flex; align-items:end; justify-content:space-between">
                                            @if(auth()->user()->admin_role == "superadmin" || auth()->user()->user_name == "admin1")
                                                <a href="{{ route('inventories.create') }}" class="btn btn-primary">Add Inventory</a>
                                            @endif
                                            <div>
                                                <p class="mb-0">Total Inventory : {{ $totalInventoryCount }}</p>
                                                <p class="mb-0">Total Onway : {{ $totalOnWayCount }}</p>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table id="example" class="table table-striped table-bordered" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Product ID</th>
                                                            <th>Product Style</th>
                                                            <th>Product Color</th>
                                                            <th>Product Size</th>
                                                            <th>Product Cost</th>
                                                            <th>Product Wholesale Price</th>
                                                            <th>Product Vendor ID</th>
                                                            <th>Product Vendor Name</th>
                                                            <th>Product Image</th>
                                                            <th>Inventory Count</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($inventoryList as $r)
                                                            @php
                                                                $prod_image = \App\Models\Product::where('product_ID', $r->product_ID)
                                                                    ->value('product_image');
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $r->product_ID }}</td>
                                                                <td>{{ ucfirst($r->product_style) }}</td>                                                           
                                                                <td>{{ $r->product_color }}</td>
                                                                <td>{{ $r->product_size }}</td>
                                                                <td>{{ $r->product_cost }}</td>
                                                                <td>{{ $r->product_wholesale_price }}</td>
                                                                <td>{{ $r->product_vendor_ID }}</td>
                                                                <td>{{ $r->product_vendor_name }}</td>
                                                                <td><img src="{{ $prod_image }}" width="100"></td>
                                                                <td>{{ $r->product_quantity }}</td>
                                                                <td class="text-center">
                                                                    <a target="_self" class="btn btn-success mb-0 btn-sm" 
                                                                        href="{{ route('inventories.edit', $r->uID) }}">Edit</a>
                                                                    @if(auth()->user()->admin_role == "superadmin" || auth()->user()->user_name == "admin1")
                                                                        {{-- <a target="_self" class="btn btn-danger mb-0 mr-0 btn-sm" 
                                                                            href="{{ route('inventories.destroy', $r->uID) }}">Delete</a> --}}

                                                                            <form action="{{ route('inventories.destroy', $r->uID) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this inventory item?');" style="display:inline;">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button type="submit" class="btn btn-danger mb-0 mr-0 btn-sm">Delete</button>
                                                                            </form>

                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th>Product ID</th>
                                                            <th>Product Style</th>
                                                            <th>Product Color</th>
                                                            <th>Product Size</th>
                                                            <th>Product Cost</th>
                                                            <th>Product Wholesale Price</th>
                                                            <th>Product Vendor ID</th>
                                                            <th>Product Vendor Name</th>
                                                            <th>Product Image</th>
                                                            <th>Inventory Count</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- [ Main Content ] end -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label>Product Quantity</label>
                    <input type="text" name="" id="product_quantity" class="form-control">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary save">Save changes</button>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
@endsection

@section('page-js')
    <!-- Required Js -->
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
    <script src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>

    <script src="/assets/js/pcoded.min.js"></script> --}}
    <!-- notification Js -->
    <script src="/assets/plugins/notification/js/bootstrap-growl.min.js"></script>

    <!-- datatable date range links -->
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
    <script src="https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#example').DataTable({
                aLengthMenu: [
                    [25, 50, 100, 200, -1],
                    [25, 50, 100, 200, "All"]
                ],
                dom: 'lBfrtip',
                buttons: [
                    'copyHtml5',
                    {
                        extend: 'print',
                        title: 'Inventory Download',
                        exportOptions: {
                            columns: [1, 2, 3, 5, 9]
                        },
                        customize: function(win) {
                            $(win.document.body).find('tr:nth-child(odd) td').each(function(index) {
                                $(this).css('background-color', '#f4f4f4');
                            });
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        title: 'Inventory Download',
                        exportOptions: {
                            columns: [1, 2, 3, 5, 9]
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        title: 'Inventory Download',
                        exportOptions: {
                            columns: [1, 2, 3, 5, 9]
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        title: 'Inventory Download',
                        exportOptions: {
                            columns: [1, 2, 3, 5, 9]
                        }
                    }
                ]
            });

            // Edit button click handler
            $('.edit_button').click(function(event) {
                var id = $(this).attr('data-id');
                datatype = "edit";
                
                $.ajax({
                    url: "{{ route('inventory.edit-quantity') }}",
                    type: "POST",
                    data: {
                        id: id,
                        datatype: datatype,
                        _token: "{{ csrf_token() }}"
                    },
                    dataType: "JSON",
                    success: function(response) {
                        $('#product_quantity').val(response.product_quantity);
                        $('.save').attr("data-id", response.uID)
                        $('#exampleModal').modal('show');
                    }
                });
            });

            // Save button click handler
            $('.save').click(function(event) {
                var id = $(this).attr('data-id');
                var quantity = $('#product_quantity').val();
                datatype = "update";
                
                $.ajax({
                    url: "{{ route('inventory.update-quantity') }}",
                    type: "POST",
                    data: {
                        id: id,
                        datatype: datatype,
                        quantity: quantity,
                        _token: "{{ csrf_token() }}"
                    },
                    dataType: "JSON",
                    success: function(response) {
                        $('#exampleModal').modal('hide');
                        location.reload();
                    }
                });
            });

            // Delete button click handler
            $('.delte').click(function(event) {
                var id = $(this).attr('data-id');
                datatype = "delete";
                
                $.ajax({
                    url: "{{ route('inventory.delete') }}",
                    type: "POST",
                    data: {
                        id: id,
                        _token: "{{ csrf_token() }}"
                    },
                    dataType: "JSON",
                    success: function(response) {
                        location.reload();
                    }
                });
            });
        });
    </script>
@endsection