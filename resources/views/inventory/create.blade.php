@extends('layouts.app')

@section('title', 'Add Inventory')

@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-wrapper">
        <div class="pcoded-content">
            <div class="pcoded-inner-content">
                <div class="main-body">
                    <div class="page-wrapper">
                        <div class="row">
                            <div class="col">
                                <form method="POST" action="{{ route('inventories.store') }}">
                                    @csrf
                                    
                                    <h5 class="mb-0 text-uppercase">Add Inventory</h5>
                                    <hr />
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row mt-4">
                                                <div class="col">
                                                    <label for="styles">Product Style</label>
                                                    <select required name="style" id="styles" class="form-control" aria-label="Default select example">
                                                        <option value="">Choose A Product Style</option>
                                                        @foreach($productStyles as $style)
                                                            <option value="{{ $style }}">{{ strtoupper($style) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col">
                                                    <label for="color">Product Color</label>
                                                    <select required name="color" id="color" class="form-control" aria-label="Default select example">
                                                        <option value="">Choose a Color</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row mt-4">
                                                <div class="col">
                                                    <label for="size">Product Size</label>
                                                    <select required name="size" id="size" class="form-control" aria-label="Default select example">
                                                        <option value="">Choose Size</option>
                                                    </select>
                                                </div>
                                                <div class="col">
                                                    <label for="quantity">Order Quantity</label>
                                                    <input required type="number" class="form-control" name="quantity" id="quantity" min="1">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-block">
                                        <button type="submit" class="btn btn-primary">Add Inventory</button>
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
<script type="text/javascript">
 console.log('1'); 
    $(document).ready(function() {
        // Get colors on style change
        console.log('2'); 
        $('#styles').change(function() {
            console.log('Style changed'); 
            $.ajax({
                type: 'POST',
                url: '{{ route("inventory.get-colors") }}',
                data: { 
                    style: $(this).val(),
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    $('#color').html(data);
                    $('#size').html('<option value="">Choose Size</option>');
                }
            });
        });

        // Get sizes on color change
        $('#color').change(function() {
            var color_get = $(this).val();
            var style_get = $('#styles').val();
            
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
                    // success: function(response) {
                    //     var min_val = response.min;
                    //     var max_val = response.max;
                        
                    //     $('#size').empty().append('<option value="">Choose Size</option>');
                    //     for (var i = parseInt(min_val); i <= parseInt(max_val); i += 2) {
                    //         $('#size').append('<option value="'+i+'">'+i+'</option>');
                    //     }
                    // }
                    success: function(response) {

    $('#size').html('<option value="">Choose Size</option>');

    if (!response.sizes || response.sizes.length === 0) {
        return;
    }

    $.each(response.sizes, function(index, size) {
        $('#size').append('<option value="' + size + '">' + size + '</option>');
    });
}
                });
            }
        });
    });
</script>
@endsection