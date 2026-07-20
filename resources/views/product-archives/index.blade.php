@extends('layouts.app')

@section('page-css')
    <link href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet" />
    
    <style>
        .badgeButton {
            color: #fff !important;
            background-color: #1de9b6 !important;
            border-color: #1de9b6 !important;
            margin-bottom: 6px;
            font-size: 14px;
        }
        .dt-buttons {
            margin-left: 20px !important;
        }
        .dataTables_wrapper {
            margin-top: 5px;
        }
    </style>
@endsection

@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-wrapper">
        <div class="pcoded-content">
            <div class="pcoded-inner-content">
                <div class="main-body">
                    <div class="page-wrapper">
                        <div class="row">
                            <div class="col">
                                <h5 class="mb-0 text-uppercase">Archive Products</h5>
                                <hr />
                                <div class="text-right mb-3 d-flex align-items-center">
                                    @if($archiveList->count() > 0)
                                        @foreach($archiveList as $archive)
                                            <form method="GET" action="{{ route('product-archives.index') }}">
                                                @csrf
                                                <input type="submit" name="action" class="btn btn-success mb-0 btn-sm" value="{{ $archive }}" />
                                            </form>
                                        @endforeach
                                    @else
                                        <input type="button" name="action" class="btn btn-danger mb-0 btn-sm" value="No Archives" />
                                    @endif
                                </div>
                                
                                @if($products->count() > 0)
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <form id="archiveForm" method="POST" action="{{ route('product-archives.restore') }}">
                                                @csrf
                                                <input type="hidden" name="archive_name" value="{{ $products->first()->first()->archive_name }}">
                                                
                                                <table id="example" class="table table-striped table-bordered" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>Check</th>
                                                            <th>Image</th>
                                                            <th>Style</th>
                                                            <th>Color</th>
                                                            <th>Size Range</th>
                                                            <th>Sub Products</th>
                                                            <th>Total Cost</th>
                                                            <th>Total Price</th>
                                                            <th>Vendor</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($products as $style => $styleProducts)
                                                        @php
                                                            $firstProduct = $styleProducts->first();
                                                        @endphp
                                                        <tr>
                                                            <td style="text-align: center;vertical-align: middle;">
                                                                <input class="form-check-input" type="checkbox" 
                                                                       value="{{ $firstProduct->product_style }}" 
                                                                       id="{{ $firstProduct->product_style }}" 
                                                                       name="products[]">
                                                                <label class="form-check-label" for="{{ $firstProduct->product_style }}"></label>
                                                            </td>
                                                            <td>
                                                                <div class="col-12 col-md-4 mx-auto" style="padding:0px;">
                                                                    <img src="{{ $firstProduct->product_image }}" alt="" class="w-100 img-fluid zoom">
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <a target="_blank" href="{{ $firstProduct->product_link }}">
                                                                    {{ strtoupper($firstProduct->product_style) }}
                                                                </a>
                                                            </td>
                                                            <td style="min-width: 20em;">
                                                                <select class="js-select2 form-control select_color" name="select_color" multiple>
                                                                    @foreach($styleProducts as $product)
                                                                    <option 
                                                                        {{ $product->product_status ? 'selected' : '' }} 
                                                                        value="{{ $product->product_ID }}" 
                                                                        data-style="{{ $product->product_style }}" 
                                                                        data-colorId="{{ $product->product_ID }}">
                                                                        {{ strtoupper($product->product_color) }}
                                                                    </option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td>{{ $firstProduct->product_size_range }}</td>
                                                                <td>{{ implode(', ', $firstProduct->sub_products ?? []) }}</td>
                                                            <td>{{ $firstProduct->product_cost }}</td>

                                                            <td>{{ $firstProduct->product_wholesale_price }}</td>
                                                            <td>{{ strtoupper($firstProduct->product_vendor_name) }}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                @if(auth()->user()->admin_role == "superadmin" || auth()->user()->user_name == "admin1")
                                <div class="card-block">
                                    <button type="submit" form="archiveForm" class="btn btn-success float-right">
                                        Restore Data
                                    </button>
                                </div>
                                @endif
                                @else
                                <div class="alert alert-info">
                                    No products found in archive. Please select an archive to view products.
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-js')
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
    <script src="https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#example').DataTable({
                aLengthMenu: [
                    [10, 25, 50, 100, 200, -1],
                    [10, 25, 50, 100, 200, "All"]
                ],
                dom: 'lBfrtip',
                buttons: [{
                    extend: 'print',
                    autoPrint: true,
                    text: 'Print',
                    exportOptions: {
                        columns: ':not(:first-child)'
                    },
                    customize: function(win) {
                        $(win.document.body)
                            .css('font-size', '11pt')
                            .find('table')
                            .addClass('compact')
                            .css('font-size', 'inherit');

                        var css = '@page { size: landscape; }';
                        var style = document.createElement('style');
                        style.type = 'text/css';
                        style.media = 'print';
                        style.appendChild(document.createTextNode(css));
                        win.document.head.appendChild(style);
                    }
                }]
            });

            // Initialize Select2
            // $(".js-select2").select2({
            //     closeOnSelect: false,
            //     placeholder: "Colors",
            //     allowHtml: true,
            //     allowClear: true,
            //     tags: false
            // });


            function initSelect2() {
                $(".js-select2").select2({
                    closeOnSelect: false,
                    placeholder: "Colors",
                    allowHtml: true,
                    allowClear: true,
                    tags: false
                });



                // Handle select/deselect events for product activation
                $('.js-select2').on('select2:select', function(e) {
                    updateProductStatus(e.params.data.id, 1, e.params.data.text);
                });

                $('.js-select2').on('select2:unselect', function(e) {
                    updateProductStatus(e.params.data.id, 0, e.params.data.text);
                });
 
            }

            initSelect2();

             table.on('draw.dt', function () {
                 console.log("On Sort");
                initSelect2();
            });

            function updateProductStatus(productId, status, colorName) {
                $.ajax({
                    url: "{{ route('product-archives.update-status', ':id') }}".replace(':id', productId),
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        status: status
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr[status ? 'success' : 'error'](response.message, status ? 'Activated' : 'Deactivated', {
                                progressBar: true,
                                closeHtml: '<button type="button">&times;</button>',
                                newestOnTop: true,
                            });
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Error updating product status', 'Error');
                    }
                });
            }

            // Show toast messages if they exist
            @if(session('success'))
                toastr.success('{{ session('success') }}');
            @endif
            @if(session('error'))
                toastr.error('{{ session('error') }}');
            @endif
        });
    </script>
@endsection