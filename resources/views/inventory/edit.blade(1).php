@extends('layouts.app')

@section('title', 'Edit Inventory')



@section('content')
    <div class="pcoded-main-container">
        <div class="pcoded-wrapper">
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">
                            <div class="row">
                                <div class="col">
                                    <form method="POST" action="{{ route('inventories.update', $inventory->uID) }}">
                                        @csrf
                                        @method('PUT')

                                        <h5 class="mb-0 text-uppercase">Edit Inventory</h5>
                                        <hr />
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row mt-4">
                                                    <div class="col">
                                                        <label for="styles">Product Style</label>
                                                        <input disabled type="text" class="form-control" id="style"
                                                            value="{{ strtoupper($inventory->product_style) }}">
                                                    </div>
                                                    <div class="col">
                                                        <label for="color">Product Color</label>
                                                        <select required name="color" id="color" class="form-control">
                                                            <option value="">Choose a Color</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row mt-4">
                                                    <div class="col">
                                                        <label for="size">Product Size</label>

                                                        <select required name="size" id="size" class="form-control">
                                                            <option value="">Choose a Size</option>
                                                        </select>

                                                        {{-- <input type="number" class="form-control" name="size" 
                                                        value="{{ $inventory->product_size }}" required> --}}
                                                    </div>
                                                    <div class="col">
                                                        <label for="quantity">Quantity</label>
                                                        <input required type="number" class="form-control" name="quantity"
                                                            value="{{ $inventory->product_quantity }}" min="1">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-block">
                                            <button type="submit" class="btn btn-primary">Update Inventory</button>
                                        </div>
                                    </form>
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
    <script>
        $(document).ready(function() {
            var currentColor = "{{ strtoupper($inventory->product_color) }}";
            var currentSize = "{{ ($inventory->product_size) }}";

            // Load colors for the current product style
            function loadColors() {
                $.ajax({
                    type: 'POST',
                    url: '{{ route('inventory.get-colors2') }}',
                    data: {
                        style: $('#style').val().toLowerCase(),
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        $('#color').html(data);

                        // Set the current color as selected
                        if (currentColor) {
                            $('#color option').each(function() {
                                if ($(this).val().toUpperCase() === currentColor
                                .toUpperCase()) {
                                    $(this).prop('selected', true);
                                    return false; // Break the loop
                                }
                            });
                        }
                         loadSize()
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                });
            }

            // Initial load of colors
            loadColors();

            // Reload colors when style changes
            $('#style').change(loadColors);


            function loadSize() {
                var style_get = $('#style').val();
                var color_get = $('#color').val();

                if (color_get && style_get) {
                    $.ajax({
                        url: "{{ route('inventory.get-sizes') }}",
                        type: "POST",
                        dataType: "JSON",
                        data: {
                            color_get: color_get,
                            style_get: style_get,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            var min_val = response.min;
                            var max_val = response.max;

                            $('#size').empty().append('<option value="">Choose Size</option>');
                            for (var i = parseInt(min_val); i <= parseInt(max_val); i += 2) {
                                let selc = currentSize == i ? "selected" : ""
                                $('#size').append('<option '+selc+' value="' + i + '">' + i + '</option>');
                            }
                        }
                    });
                }
            }

            // $('#color').change(function() {
            //     loadSize()
            // });
        });
    </script>
@endsection
